<?php

namespace App\Http\Controllers;

use App\Models\TenantPaymentAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfilePaymentMethodController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $tenant = $request->user();
        $payload = $this->validatedPayload($request);

        DB::transaction(function () use ($request, $tenant, &$payload): void {
            if ($payload['is_primary']) {
                $tenant->paymentAccounts()->update(['is_primary' => false]);
            }

            if ($request->hasFile('qr_code')) {
                $payload['qr_code_path'] = $request->file('qr_code')?->store('tenants/'.$tenant->id.'/payment-methods', 'public');
            }

            $tenant->paymentAccounts()->create($payload);
            $this->ensurePrimary($tenant);
            $this->syncLegacyPrimary($tenant);
        });

        return back()->with('status', 'payment-method-created');
    }

    public function update(Request $request, TenantPaymentAccount $paymentMethod): RedirectResponse
    {
        $tenant = $request->user();
        $paymentMethod = $this->ownedMethod($tenant, $paymentMethod);
        $payload = $this->validatedPayload($request, $paymentMethod);
        $oldQrPath = $paymentMethod->qr_code_path;

        DB::transaction(function () use ($request, $tenant, $paymentMethod, &$payload): void {
            if ($payload['is_primary']) {
                $tenant->paymentAccounts()->whereKeyNot($paymentMethod->id)->update(['is_primary' => false]);
            }

            if ($request->hasFile('qr_code')) {
                $payload['qr_code_path'] = $request->file('qr_code')?->store('tenants/'.$tenant->id.'/payment-methods', 'public');
            } elseif ($payload['type'] !== TenantPaymentAccount::TYPE_QRIS) {
                $payload['qr_code_path'] = null;
            }

            $paymentMethod->update($payload);
            $this->ensurePrimary($tenant);
            $this->syncLegacyPrimary($tenant);
        });

        if ($oldQrPath && $oldQrPath !== $paymentMethod->fresh()->qr_code_path) {
            Storage::disk('public')->delete($oldQrPath);
        }

        return back()->with('status', 'payment-method-updated');
    }

    public function toggle(Request $request, TenantPaymentAccount $paymentMethod): RedirectResponse
    {
        $tenant = $request->user();
        $paymentMethod = $this->ownedMethod($tenant, $paymentMethod);

        DB::transaction(function () use ($tenant, $paymentMethod): void {
            $wasActive = $paymentMethod->is_active;
            $paymentMethod->update([
                'is_active' => ! $wasActive,
                'is_primary' => $wasActive ? false : $paymentMethod->is_primary,
            ]);
            $this->ensurePrimary($tenant);
            $this->syncLegacyPrimary($tenant);
        });

        return back()->with('status', 'payment-method-toggled');
    }

    public function primary(Request $request, TenantPaymentAccount $paymentMethod): RedirectResponse
    {
        $tenant = $request->user();
        $paymentMethod = $this->ownedMethod($tenant, $paymentMethod);

        if (! $paymentMethod->is_active) {
            return back()->withErrors(['payment_method' => 'Aktifkan metode sebelum menjadikannya utama.']);
        }

        DB::transaction(function () use ($tenant, $paymentMethod): void {
            $tenant->paymentAccounts()->update(['is_primary' => false]);
            $paymentMethod->update(['is_primary' => true]);
            $this->syncLegacyPrimary($tenant);
        });

        return back()->with('status', 'payment-method-primary');
    }

    public function destroy(Request $request, TenantPaymentAccount $paymentMethod): RedirectResponse
    {
        $tenant = $request->user();
        $paymentMethod = $this->ownedMethod($tenant, $paymentMethod);
        $qrPath = $paymentMethod->qr_code_path;

        DB::transaction(function () use ($tenant, $paymentMethod): void {
            $paymentMethod->delete();
            $this->ensurePrimary($tenant);
            $this->syncLegacyPrimary($tenant);
        });

        if ($qrPath) {
            Storage::disk('public')->delete($qrPath);
        }

        return back()->with('status', 'payment-method-deleted');
    }

    /** @return array<string, mixed> */
    private function validatedPayload(Request $request, ?TenantPaymentAccount $method = null): array
    {
        $type = (string) $request->input('type', TenantPaymentAccount::TYPE_BANK);
        $qrRequired = $type === TenantPaymentAccount::TYPE_QRIS && ($method === null || ! $method->qr_code_path);

        $data = $request->validate([
            'type' => ['required', Rule::in([
                TenantPaymentAccount::TYPE_BANK,
                TenantPaymentAccount::TYPE_EWALLET,
                TenantPaymentAccount::TYPE_QRIS,
            ])],
            'bank_name' => ['required', 'string', 'max:120'],
            'account_name' => ['nullable', 'string', 'max:120', 'required_unless:type,qris'],
            'account_number' => ['nullable', 'string', 'max:120', 'required_unless:type,qris'],
            'contact' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'qr_code' => [Rule::requiredIf($qrRequired), 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        return [
            'type' => (string) $data['type'],
            'bank_name' => trim((string) $data['bank_name']),
            'account_name' => $this->nullableString($data['account_name'] ?? null),
            'account_number' => $this->nullableString($data['account_number'] ?? null),
            'contact' => $this->nullableString($data['contact'] ?? null),
            'notes' => $this->nullableString($data['notes'] ?? null),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
            'is_primary' => $request->boolean('is_primary') && $request->boolean('is_active'),
        ];
    }

    private function ownedMethod($tenant, TenantPaymentAccount $paymentMethod): TenantPaymentAccount
    {
        return $tenant->paymentAccounts()->whereKey($paymentMethod->id)->firstOrFail();
    }

    private function ensurePrimary($tenant): void
    {
        if ($tenant->paymentAccounts()->active()->where('is_primary', true)->exists()) {
            return;
        }

        $tenant->paymentAccounts()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first()
            ?->update(['is_primary' => true]);
    }

    private function syncLegacyPrimary($tenant): void
    {
        $primary = $tenant->paymentAccounts()->active()->where('is_primary', true)->first();
        $tenant->forceFill([
            'payment_bank_name' => $primary?->bank_name,
            'payment_account_name' => $primary?->account_name,
            'payment_account_number' => $primary?->account_number,
            'payment_contact' => $primary?->contact,
        ])->save();
    }

    private function nullableString(?string $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}