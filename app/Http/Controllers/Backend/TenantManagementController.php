<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionUpgradeRequest;
use App\Models\Subscription;
use App\Models\User;
use App\Services\BackendAuditLogService;
use App\Services\PlanService;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenantManagementController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly PlanService $planService,
        private readonly BackendAuditLogService $auditLogService
    ) {
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('q', ''));
        $allowedPlans = $this->planService->allowedPlans();
        $planFilter = strtolower(trim((string) $request->string('plan', '')));
        if (! in_array($planFilter, $allowedPlans, true)) {
            $planFilter = '';
        }

        $statusFilter = strtolower(trim((string) $request->string('status', '')));
        if (! in_array($statusFilter, ['active', 'suspended', 'upgrade'], true)) {
            $statusFilter = '';
        }

        $baseTenant = User::query()->where('role', '!=', 'super_admin');
        $totalCount = (clone $baseTenant)->count();
        $suspendedCount = (clone $baseTenant)->where('is_suspended', true)->count();
        $upgradeCount = (clone $baseTenant)
            ->whereHas('subscriptionUpgradeRequests', fn ($q) => $q->where('status', SubscriptionUpgradeRequest::STATUS_PENDING_VERIFICATION))
            ->count();
        $stats = [
            'total' => $totalCount,
            'active' => $totalCount - $suspendedCount,
            'suspended' => $suspendedCount,
            'upgrade' => $upgradeCount,
        ];

        $tenants = User::query()
            ->where('role', '!=', 'super_admin')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when($planFilter !== '', function ($query) use ($planFilter): void {
                if ($planFilter === 'free') {
                    $query->where(function ($nested): void {
                        $nested->whereDoesntHave('subscription')
                            ->orWhereHas('subscription', fn ($sub) => $sub->where('plan', 'free'));
                    });
                } else {
                    $query->whereHas('subscription', fn ($sub) => $sub->where('plan', $planFilter));
                }
            })
            ->when($statusFilter === 'active', fn ($query) => $query->where('is_suspended', false))
            ->when($statusFilter === 'suspended', fn ($query) => $query->where('is_suspended', true))
            ->when($statusFilter === 'upgrade', fn ($query) => $query->whereHas('subscriptionUpgradeRequests', fn ($sub) => $sub->where('status', SubscriptionUpgradeRequest::STATUS_PENDING_VERIFICATION)))
            ->with('subscription')
            ->with('subscriptionUpgradeRequests')
            ->withCount(['services', 'customers', 'bookings'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.tenants.index', [
            'tenants' => $tenants,
            'plans' => $allowedPlans,
            'roles' => $this->allowedRoles(),
            'search' => $search,
            'planFilter' => $planFilter,
            'statusFilter' => $statusFilter,
            'stats' => $stats,
        ]);
    }

    public function quickUpdate(Request $request, User $tenant): RedirectResponse
    {
        abort_if($tenant->isSuperAdmin(), 404);

        $data = $request->validate([
            'role' => ['required', 'string', Rule::in($this->allowedRoles())],
            'plan' => ['required', 'string', Rule::in($this->planService->allowedPlans())],
        ]);

        $oldRole = $tenant->role;
        $oldPlan = $this->subscriptionService->getCurrentPlanForUser((int) $tenant->id);

        if ($tenant->role !== $data['role']) {
            $tenant->forceFill(['role' => $data['role']])->save();
        }

        if ($oldPlan !== $data['plan']) {
            $this->subscriptionService->upgradePlan((int) $tenant->id, (string) $data['plan']);
        }

        $this->auditLogService->log(
            action: 'tenant_quick_updated',
            targetType: User::class,
            targetId: (int) $tenant->id,
            targetLabel: $tenant->email,
            meta: [
                'old_role' => $oldRole,
                'new_role' => $data['role'],
                'old_plan' => $oldPlan,
                'new_plan' => $data['plan'],
            ],
            request: $request
        );

        return redirect()
            ->route('backend.tenants.index', $request->only('q', 'plan', 'page'))
            ->with('success', 'Paket & role '.$tenant->name.' berhasil diperbarui.');
    }

    public function create(): View
    {
        return view('backend.tenants.create', [
            'plans' => $this->planService->allowedPlans(),
            'roles' => $this->allowedRoles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in($this->allowedRoles())],
            'plan' => ['required', 'string', Rule::in($this->planService->allowedPlans())],
        ]);

        $tenant = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make((string) $data['password']),
            'role' => $data['role'],
        ]);

        $this->subscriptionService->upgradePlan((int) $tenant->id, (string) $data['plan']);
        $this->auditLogService->log(
            action: 'tenant_created',
            targetType: User::class,
            targetId: (int) $tenant->id,
            targetLabel: $tenant->email,
            meta: [
                'role' => $tenant->role,
                'plan' => $data['plan'],
            ],
            request: $request
        );

        return redirect()
            ->route('backend.tenants.edit', $tenant)
            ->with('success', 'Tenant baru berhasil dibuat.');
    }

    public function edit(User $tenant): View
    {
        abort_if($tenant->isSuperAdmin(), 404);
        $this->expireUnverifiedRequests((int) $tenant->id);

        $tenant->loadCount(['services', 'customers', 'bookings']);
        $subscription = $this->subscriptionService->ensureUserSubscription((int) $tenant->id);
        $upgradeRequests = SubscriptionUpgradeRequest::query()
            ->where('tenant_id', (int) $tenant->id)
            ->with('reviewer:id,name,email')
            ->latest()
            ->get();

        return view('backend.tenants.edit', [
            'tenant' => $tenant,
            'subscription' => $subscription,
            'plans' => $this->planService->allowedPlans(),
            'roles' => $this->allowedRoles(),
            'upgradeRequests' => $upgradeRequests,
        ]);
    }

    public function update(Request $request, User $tenant): RedirectResponse
    {
        abort_if($tenant->isSuperAdmin(), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($tenant->id)],
            'role' => ['required', 'string', Rule::in($this->allowedRoles())],
        ]);

        $oldRole = $tenant->role;

        $tenant->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
        ])->save();

        $this->auditLogService->log(
            action: 'tenant_updated',
            targetType: User::class,
            targetId: (int) $tenant->id,
            targetLabel: $tenant->email,
            meta: [
                'old_role' => $oldRole,
                'new_role' => $tenant->role,
            ],
            request: $request
        );

        return redirect()
            ->route('backend.tenants.edit', $tenant)
            ->with('success', 'Data tenant berhasil diperbarui.');
    }

    public function destroy(Request $request, User $tenant): RedirectResponse
    {
        abort_if($tenant->isSuperAdmin(), 404);

        $tenantLabel = $tenant->email;
        $tenantId = (int) $tenant->id;

        $tenant->delete();

        $this->auditLogService->log(
            action: 'tenant_deleted',
            targetType: User::class,
            targetId: $tenantId,
            targetLabel: $tenantLabel,
            request: $request
        );

        return redirect()
            ->route('backend.tenants.index')
            ->with('success', 'Tenant berhasil dihapus.');
    }

    public function updateSubscription(Request $request, User $tenant): RedirectResponse
    {
        abort_if($tenant->isSuperAdmin(), 404);

        $data = $request->validate([
            'plan' => ['required', 'string', Rule::in($this->planService->allowedPlans())],
            'expired_at' => ['nullable', 'date'],
            'bookings_consumed_total' => ['nullable', 'integer', 'min:0'],
        ]);

        $expiresAt = isset($data['expired_at']) && $data['expired_at'] !== null
            ? Carbon::parse($data['expired_at'])->endOfDay()
            : null;

        $this->subscriptionService->upgradePlan((int) $tenant->id, (string) $data['plan'], $expiresAt);

        if (array_key_exists('bookings_consumed_total', $data) && $data['bookings_consumed_total'] !== null) {
            Subscription::query()
                ->where('user_id', $tenant->id)
                ->update([
                    'bookings_consumed_total' => (int) $data['bookings_consumed_total'],
                ]);
        }

        $this->auditLogService->log(
            action: 'tenant_subscription_updated',
            targetType: User::class,
            targetId: (int) $tenant->id,
            targetLabel: $tenant->email,
            meta: [
                'plan' => $data['plan'],
                'expired_at' => $expiresAt?->toDateTimeString(),
                'bookings_consumed_total' => isset($data['bookings_consumed_total']) ? (int) $data['bookings_consumed_total'] : null,
            ],
            request: $request
        );

        return redirect()
            ->route('backend.tenants.edit', $tenant)
            ->with('success', 'Subscription tenant berhasil diperbarui.');
    }

    public function updateRole(Request $request, User $tenant): RedirectResponse
    {
        abort_if($tenant->isSuperAdmin(), 404);

        $data = $request->validate([
            'role' => ['required', 'string', Rule::in($this->allowedRoles())],
        ]);

        $oldRole = $tenant->role;
        $tenant->forceFill(['role' => $data['role']])->save();

        $this->auditLogService->log(
            action: 'tenant_role_updated',
            targetType: User::class,
            targetId: (int) $tenant->id,
            targetLabel: $tenant->email,
            meta: [
                'old_role' => $oldRole,
                'new_role' => $tenant->role,
            ],
            request: $request
        );

        return redirect()
            ->route('backend.tenants.edit', $tenant)
            ->with('success', 'Role tenant berhasil diperbarui.');
    }

    public function updateSuspend(Request $request, User $tenant): RedirectResponse
    {
        abort_if($tenant->isSuperAdmin(), 404);

        $data = $request->validate([
            'is_suspended' => ['required', 'boolean'],
            'suspended_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $suspend = (bool) $data['is_suspended'];

        $tenant->forceFill([
            'is_suspended' => $suspend,
            'suspended_at' => $suspend ? now() : null,
            'suspended_reason' => $suspend ? (trim((string) ($data['suspended_reason'] ?? '')) ?: 'Disuspend oleh super admin.') : null,
        ])->save();

        if ($suspend) {
            DB::table('sessions')
                ->where('user_id', $tenant->id)
                ->delete();
        }

        $this->auditLogService->log(
            action: $suspend ? 'tenant_suspended' : 'tenant_unsuspended',
            targetType: User::class,
            targetId: (int) $tenant->id,
            targetLabel: $tenant->email,
            meta: [
                'reason' => $tenant->suspended_reason,
            ],
            request: $request
        );

        return redirect()
            ->back()
            ->with('success', $suspend ? 'Tenant berhasil disuspend.' : 'Tenant berhasil diaktifkan kembali.');
    }

    public function resetPassword(Request $request, User $tenant): RedirectResponse
    {
        abort_if($tenant->isSuperAdmin(), 404);

        $data = $request->validate([
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $newPassword = trim((string) ($data['password'] ?? ''));
        if ($newPassword === '') {
            $newPassword = Str::password(12);
        }

        $tenant->forceFill([
            'password' => Hash::make($newPassword),
        ])->save();

        $this->auditLogService->log(
            action: 'tenant_password_reset',
            targetType: User::class,
            targetId: (int) $tenant->id,
            targetLabel: $tenant->email,
            request: $request
        );

        return redirect()
            ->route('backend.tenants.edit', $tenant)
            ->with('success', 'Password tenant berhasil direset.')
            ->with('tenant_new_password', $newPassword);
    }

    public function approveUpgradeRequest(Request $request, User $tenant, SubscriptionUpgradeRequest $upgradeRequest): RedirectResponse
    {
        abort_if($tenant->isSuperAdmin(), 404);
        abort_if((int) $upgradeRequest->tenant_id !== (int) $tenant->id, 404);
        $this->expireUnverifiedRequests((int) $tenant->id);
        $upgradeRequest->refresh();

        if ($upgradeRequest->status !== SubscriptionUpgradeRequest::STATUS_PENDING_VERIFICATION) {
            return redirect()
                ->route('backend.tenants.edit', $tenant)
                ->with('error', 'Permintaan upgrade ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'review_note' => ['required', 'string', 'max:1000'],
        ]);

        // Perpanjang dari sisa masa aktif jika masih berlaku, agar renewal menambah durasi.
        $currentExpiry = $this->subscriptionService->ensureUserSubscription((int) $tenant->id)->expired_at;
        $baseExpiry = ($currentExpiry && $currentExpiry->isFuture()) ? $currentExpiry->copy() : now();
        $newExpiry = $baseExpiry->addMonth();

        $this->subscriptionService->upgradePlan(
            (int) $tenant->id,
            (string) $upgradeRequest->requested_plan,
            $newExpiry
        );

        $tenant->forceFill([
            'plan_activation_notice_until' => now()->addDay(),
            'plan_activation_notice_plan' => (string) $upgradeRequest->requested_plan,
        ])->save();

        $upgradeRequest->forceFill([
            'status' => SubscriptionUpgradeRequest::STATUS_APPROVED,
            'reviewed_by' => (int) auth()->id(),
            'reviewed_at' => now(),
            'approved_at' => now(),
            'review_note' => trim((string) ($validated['review_note'] ?? '')) ?: null,
        ])->save();

        $this->auditLogService->log(
            action: 'tenant_upgrade_request_approved',
            targetType: User::class,
            targetId: (int) $tenant->id,
            targetLabel: $tenant->email,
            meta: [
                'request_id' => (int) $upgradeRequest->id,
                'from_plan' => $upgradeRequest->current_plan,
                'to_plan' => $upgradeRequest->requested_plan,
            ],
            request: $request
        );

        return redirect()
            ->route('backend.tenants.edit', $tenant)
            ->with('success', 'Permintaan upgrade disetujui dan paket tenant berhasil diperbarui.');
    }

    public function rejectUpgradeRequest(Request $request, User $tenant, SubscriptionUpgradeRequest $upgradeRequest): RedirectResponse
    {
        abort_if($tenant->isSuperAdmin(), 404);
        abort_if((int) $upgradeRequest->tenant_id !== (int) $tenant->id, 404);
        $this->expireUnverifiedRequests((int) $tenant->id);
        $upgradeRequest->refresh();

        if ($upgradeRequest->status !== SubscriptionUpgradeRequest::STATUS_PENDING_VERIFICATION) {
            return redirect()
                ->route('backend.tenants.edit', $tenant)
                ->with('error', 'Permintaan upgrade ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'review_note' => ['required', 'string', 'max:1000'],
        ]);

        $upgradeRequest->forceFill([
            'status' => SubscriptionUpgradeRequest::STATUS_REJECTED,
            'reviewed_by' => (int) auth()->id(),
            'reviewed_at' => now(),
            'review_note' => trim((string) $validated['review_note']),
        ])->save();

        $this->auditLogService->log(
            action: 'tenant_upgrade_request_rejected',
            targetType: User::class,
            targetId: (int) $tenant->id,
            targetLabel: $tenant->email,
            meta: [
                'request_id' => (int) $upgradeRequest->id,
                'from_plan' => $upgradeRequest->current_plan,
                'to_plan' => $upgradeRequest->requested_plan,
            ],
            request: $request
        );

        return redirect()
            ->route('backend.tenants.edit', $tenant)
            ->with('success', 'Permintaan upgrade berhasil ditolak.');
    }

    /**
     * @return array<int, string>
     */
    private function allowedRoles(): array
    {
        return ['tenant', 'manager', 'staff'];
    }

    private function expireUnverifiedRequests(int $tenantId): void
    {
        $expiredAt = now()->subDay();
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
                'reviewed_at' => now(),
                'reviewed_by' => null,
                'review_note' => $item->review_note ?: 'Request kedaluwarsa otomatis karena tidak selesai diverifikasi dalam 1x24 jam.',
            ])->save();
        }
    }
}
