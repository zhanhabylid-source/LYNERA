<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillingLog;
use App\Models\SubscriptionUpgradeRequest;
use App\Models\SubscriptionPaymentMethod;
use App\Services\PlanService;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function __construct(
        private readonly PlanService $planService,
        private readonly SubscriptionService $subscriptionService
    ) {
    }

    public function index(): View
    {
        $user = auth()->user();
        $tenantId = (int) ($user?->id ?? 0);
        $this->expireUnverifiedRequests($tenantId);
        $plan = $this->planService->normalizePlan(getUserPlan($user));
        $trialDaysLeft = getRemainingDays($user);
        $expiresAt = $user?->subscription?->expired_at;
        $bookingUsage = $this->subscriptionService->getBookingUsage((int) ($user?->id ?? 0));
        $plans = collect($this->planService->all())
            ->map(fn (array $detail, string $key) => $this->planService->detail($key))
            ->all();
        $availableUpgradePlans = collect($plans)
            ->reject(fn (array $detail, string $key): bool => $key === $plan)
            ->all();
        $upgradeRequests = SubscriptionUpgradeRequest::query()
            ->where('tenant_id', $tenantId)
            ->latest()
            ->get();
        $hasPendingUpgradeRequest = $upgradeRequests
            ->contains(fn (SubscriptionUpgradeRequest $item): bool => in_array($item->status, [
                SubscriptionUpgradeRequest::STATUS_PENDING_PAYMENT,
                SubscriptionUpgradeRequest::STATUS_PENDING_VERIFICATION,
            ], true));
        $pendingPaymentRequests = $upgradeRequests
            ->filter(fn (SubscriptionUpgradeRequest $item): bool => $item->status === SubscriptionUpgradeRequest::STATUS_PENDING_PAYMENT)
            ->values();
        $subscriptionHistories = BillingLog::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('event_type', 'subscription_upgrade')
            ->latest()
            ->get();
        $paymentMethods = SubscriptionPaymentMethod::query()
            ->active()
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('billing.index', [
            'plan' => $plan,
            'trialDaysLeft' => $trialDaysLeft,
            'expiresAt' => $expiresAt,
            'currentPlan' => $plans[$plan] ?? $this->planService->detail($this->planService->defaultPlan()),
            'plans' => $plans,
            'availableUpgradePlans' => $availableUpgradePlans,
            'bookingUsage' => $bookingUsage,
            'upgradeRequests' => $upgradeRequests,
            'pendingPaymentRequests' => $pendingPaymentRequests,
            'hasPendingUpgradeRequest' => $hasPendingUpgradeRequest,
            'subscriptionHistories' => $subscriptionHistories,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function requestUpgrade(Request $request): RedirectResponse
    {
        $tenant = auth()->user();
        $tenantId = (int) ($tenant?->id ?? 0);
        $this->expireUnverifiedRequests($tenantId);
        $currentPlan = $this->planService->normalizePlan(getUserPlan($tenant));

        $allowedTargetPlans = collect($this->planService->allowedPlans())
            ->reject(fn (string $plan): bool => $plan === 'free')
            ->values()
            ->all();

        if ($tenant === null || $tenantId <= 0) {
            return redirect()
                ->route('billing.index')
                ->with('error', 'Tenant tidak ditemukan.');
        }

        $hasPending = SubscriptionUpgradeRequest::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', [
                SubscriptionUpgradeRequest::STATUS_PENDING_PAYMENT,
                SubscriptionUpgradeRequest::STATUS_PENDING_VERIFICATION,
            ])
            ->exists();
        if ($hasPending) {
            return redirect()
                ->route('billing.index')
                ->with('error', 'Masih ada permintaan upgrade yang menunggu validasi super admin.');
        }

        $data = $request->validate([
            'requested_plan' => ['required', 'string', Rule::in($allowedTargetPlans)],
            'request_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $requestedPlan = $this->planService->detail((string) $data['requested_plan']);

        SubscriptionUpgradeRequest::query()->create([
            'tenant_id' => $tenantId,
            'current_plan' => $currentPlan,
            'requested_plan' => (string) $data['requested_plan'],
            'requested_price' => (string) ($requestedPlan['effective_price'] ?? $requestedPlan['price'] ?? '-'),
            'status' => SubscriptionUpgradeRequest::STATUS_PENDING_PAYMENT,
            'payment_method' => '-',
            'payer_name' => '-',
            'payer_account_number' => null,
            'payment_note' => trim((string) ($data['request_note'] ?? '')) ?: null,
            'proof_path' => '-',
        ]);

        return redirect()
            ->route('billing.index')
            ->with('success', 'Request upgrade berhasil dibuat. Silakan lanjutkan konfirmasi pembayaran dalam 1x24 jam.');
    }

    public function confirmUpgradePayment(Request $request, SubscriptionUpgradeRequest $upgradeRequest): RedirectResponse
    {
        $tenant = auth()->user();
        $tenantId = (int) ($tenant?->id ?? 0);
        if ($tenantId <= 0 || (int) $upgradeRequest->tenant_id !== $tenantId) {
            return redirect()
                ->route('billing.index')
                ->with('error', 'Request upgrade tidak ditemukan.');
        }

        $this->expireUnverifiedRequests($tenantId);
        $upgradeRequest->refresh();
        if ($upgradeRequest->status !== SubscriptionUpgradeRequest::STATUS_PENDING_PAYMENT) {
            return redirect()
                ->route('billing.index')
                ->with('error', 'Request ini tidak dapat dikonfirmasi pembayaran.');
        }

        $data = $request->validate([
            'payment_method' => ['required', 'string', 'max:120'],
            'payer_name' => ['required', 'string', 'max:120'],
            'payer_account_number' => ['nullable', 'string', 'max:80'],
            'payment_note' => ['nullable', 'string', 'max:2000'],
            'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $proofPath = $request->file('payment_proof')?->store('upgrade-proofs', 'public');
        if ($proofPath === null) {
            return redirect()
                ->route('billing.index')
                ->with('error', 'Bukti pembayaran gagal diunggah.');
        }
        $proofPath = str_replace('\\', '/', $proofPath);

        $upgradeRequest->forceFill([
            'status' => SubscriptionUpgradeRequest::STATUS_PENDING_VERIFICATION,
            'payment_method' => trim((string) $data['payment_method']),
            'payer_name' => trim((string) $data['payer_name']),
            'payer_account_number' => trim((string) ($data['payer_account_number'] ?? '')) ?: null,
            'payment_note' => trim((string) ($data['payment_note'] ?? '')) ?: null,
            'proof_path' => $proofPath,
        ])->save();

        return redirect()
            ->route('billing.index')
            ->with('success', 'Konfirmasi pembayaran berhasil dikirim. Request Anda sedang menunggu validasi super admin.');
    }

    private function expireUnverifiedRequests(int $tenantId): void
    {
        if ($tenantId <= 0) {
            return;
        }

        $expiredAt = Carbon::now()->subDay();
        $requests = SubscriptionUpgradeRequest::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', [
                SubscriptionUpgradeRequest::STATUS_PENDING_PAYMENT,
                SubscriptionUpgradeRequest::STATUS_PENDING_VERIFICATION,
            ])
            ->where('created_at', '<=', $expiredAt)
            ->get();

        foreach ($requests as $item) {
            if ($item->proof_path && $item->proof_path !== '-') {
                Storage::disk('public')->delete($item->proof_path);
            }

            $item->forceFill([
                'status' => SubscriptionUpgradeRequest::STATUS_EXPIRED,
                'review_note' => $item->review_note ?: 'Request kedaluwarsa otomatis karena tidak selesai diverifikasi dalam 1x24 jam.',
                'reviewed_at' => now(),
                'reviewed_by' => null,
            ])->save();
        }
    }
}
