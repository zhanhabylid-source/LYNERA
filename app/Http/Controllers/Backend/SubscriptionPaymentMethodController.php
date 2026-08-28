<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPaymentMethod;
use App\Services\BackendAuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubscriptionPaymentMethodController extends Controller
{
    public function __construct(
        private readonly BackendAuditLogService $auditLogService
    ) {
    }

    public function index(): View
    {
        return view('backend.payment-methods.index', [
            'paymentMethods' => SubscriptionPaymentMethod::query()
                ->orderByDesc('is_primary')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatedPayload($request);

        $method = DB::transaction(function () use ($request, $payload): SubscriptionPaymentMethod {
            if ($payload['is_primary']) {
                SubscriptionPaymentMethod::query()->update(['is_primary' => false]);
            }

            if ($request->hasFile('qr_code')) {
                $payload['qr_code_path'] = $request->file('qr_code')?->store('subscription-payment-methods', 'public');
            }

            $method = SubscriptionPaymentMethod::query()->create($payload);
            $this->ensurePrimaryMethod();

            return $method;
        });

        $this->auditLogService->log(
            action: 'subscription_payment_method_created',
            targetType: SubscriptionPaymentMethod::class,
            targetId: (int) $method->id,
            targetLabel: $method->displayLabel(),
            request: $request
        );

        return back()->with('success', 'Metode pembayaran upgrade berhasil ditambahkan.');
    }

    public function update(Request $request, SubscriptionPaymentMethod $paymentMethod): RedirectResponse
    {
        $payload = $this->validatedPayload($request, $paymentMethod);
        $oldQrPath = $paymentMethod->qr_code_path;

        DB::transaction(function () use ($request, $paymentMethod, &$payload): void {
            if ($payload['is_primary']) {
                SubscriptionPaymentMethod::query()
                    ->whereKeyNot($paymentMethod->id)
                    ->update(['is_primary' => false]);
            }

            if ($request->hasFile('qr_code')) {
                $payload['qr_code_path'] = $request->file('qr_code')?->store('subscription-payment-methods', 'public');
            } elseif ($payload['type'] !== SubscriptionPaymentMethod::TYPE_QRIS) {
                $payload['qr_code_path'] = null;
            }

            $paymentMethod->update($payload);
            $this->ensurePrimaryMethod();
        });

        if ($oldQrPath && $oldQrPath !== $paymentMethod->fresh()->qr_code_path) {
            Storage::disk('public')->delete($oldQrPath);
        }

        $this->auditLogService->log(
            action: 'subscription_payment_method_updated',
            targetType: SubscriptionPaymentMethod::class,
            targetId: (int) $paymentMethod->id,
            targetLabel: $paymentMethod->displayLabel(),
            request: $request
        );

        return back()->with('success', 'Metode pembayaran upgrade berhasil diperbarui.');
    }

    public function toggle(Request $request, SubscriptionPaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->update([
            'is_active' => ! $paymentMethod->is_active,
            'is_primary' => $paymentMethod->is_active ? false : $paymentMethod->is_primary,
        ]);
        $this->ensurePrimaryMethod();

        $this->auditLogService->log(
            action: 'subscription_payment_method_toggled',
            targetType: SubscriptionPaymentMethod::class,
            targetId: (int) $paymentMethod->id,
            targetLabel: $paymentMethod->displayLabel(),
            meta: ['is_active' => $paymentMethod->fresh()->is_active],
            request: $request
        );

        return back()->with('success', 'Status metode pembayaran berhasil diperbarui.');
    }

    public function primary(Request $request, SubscriptionPaymentMethod $paymentMethod): RedirectResponse
    {
        if (! $paymentMethod->is_active) {
            return back()->with('error', 'Aktifkan metode pembayaran sebelum menjadikannya metode utama.');
        }

        DB::transaction(function () use ($paymentMethod): void {
            SubscriptionPaymentMethod::query()->update(['is_primary' => false]);
            $paymentMethod->update(['is_primary' => true]);
        });

        $this->auditLogService->log(
            action: 'subscription_payment_method_primary_set',
            targetType: SubscriptionPaymentMethod::class,
            targetId: (int) $paymentMethod->id,
            targetLabel: $paymentMethod->displayLabel(),
            request: $request
        );

        return back()->with('success', 'Metode pembayaran utama berhasil dipilih.');
    }

    public function destroy(Request $request, SubscriptionPaymentMethod $paymentMethod): RedirectResponse
    {
        $label = $paymentMethod->displayLabel();
        $qrPath = $paymentMethod->qr_code_path;

        $paymentMethod->delete();
        $this->ensurePrimaryMethod();

        if ($qrPath) {
            Storage::disk('public')->delete($qrPath);
        }

        $this->auditLogService->log(
            action: 'subscription_payment_method_deleted',
            targetType: SubscriptionPaymentMethod::class,
            targetId: (int) $paymentMethod->id,
            targetLabel: $label,
            request: $request
        );

        return back()->with('success', 'Metode pembayaran upgrade berhasil dihapus.');
    }

    /** @return array<string, mixed> */
    private function validatedPayload(Request $request, ?SubscriptionPaymentMethod $method = null): array
    {
        $qrRequired = $request->string('type')->toString() === SubscriptionPaymentMethod::TYPE_QRIS
            && ($method === null || ! $method->qr_code_path);

        $data = $request->validate([
            'type' => ['required', Rule::in([
                SubscriptionPaymentMethod::TYPE_BANK,
                SubscriptionPaymentMethod::TYPE_EWALLET,
                SubscriptionPaymentMethod::TYPE_QRIS,
            ])],
            'provider_name' => ['required', 'string', 'max:120'],
            'account_name' => ['nullable', 'string', 'max:120'],
            'account_number' => ['nullable', 'string', 'max:120', 'required_unless:type,qris'],
            'contact' => ['nullable', 'string', 'max:120'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'qr_code' => [Rule::requiredIf($qrRequired), 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        return [
            'type' => (string) $data['type'],
            'provider_name' => trim((string) $data['provider_name']),
            'account_name' => $this->nullableString($data['account_name'] ?? null),
            'account_number' => $this->nullableString($data['account_number'] ?? null),
            'contact' => $this->nullableString($data['contact'] ?? null),
            'instructions' => $this->nullableString($data['instructions'] ?? null),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
            'is_primary' => $request->boolean('is_primary') && $request->boolean('is_active'),
        ];
    }

    private function ensurePrimaryMethod(): void
    {
        $hasPrimary = SubscriptionPaymentMethod::query()
            ->active()
            ->where('is_primary', true)
            ->exists();

        if ($hasPrimary) {
            return;
        }

        SubscriptionPaymentMethod::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first()
            ?->update(['is_primary' => true]);
    }

    private function nullableString(?string $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}