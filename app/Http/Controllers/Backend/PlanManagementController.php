<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\PlanOverride;
use App\Services\BackendAuditLogService;
use App\Services\PlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlanManagementController extends Controller
{
    public function __construct(
        private readonly PlanService $planService,
        private readonly BackendAuditLogService $auditLogService
    ) {
    }

    public function index(): View
    {
        $basePlans = (array) config('plans.plans', []);
        $overrides = PlanOverride::query()
            ->get()
            ->keyBy(fn (PlanOverride $override) => strtolower((string) $override->plan_key));

        $plans = collect($this->planService->allowedPlans())
            ->map(function (string $planKey) use ($basePlans, $overrides): array {
                return [
                    'key' => $planKey,
                    'base' => (array) ($basePlans[$planKey] ?? []),
                    'effective' => $this->planService->detail($planKey),
                    'override' => $overrides->get($planKey),
                ];
            })
            ->all();

        return view('backend.plans.index', [
            'plans' => $plans,
        ]);
    }

    public function update(Request $request, string $planKey): RedirectResponse
    {
        $normalizedPlan = $this->planService->normalizePlan($planKey);
        abort_unless($this->planService->isKnownPlan($normalizedPlan), 404);

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'price' => ['nullable', 'string', 'max:120'],
            'promo_price' => ['nullable', 'string', 'max:120'],
            'promo_label' => ['nullable', 'string', 'max:120'],
            'promo_is_active' => ['nullable', 'boolean'],
            'promo_starts_at' => ['nullable', 'date', 'required_with:promo_ends_at'],
            'promo_ends_at' => ['nullable', 'date', 'after:promo_starts_at'],
            'billing_cycle' => ['nullable', 'string', 'max:120'],
            'booking_limit_total' => ['nullable', 'integer', 'min:0'],
            'benefit' => ['nullable', 'string', 'max:2000'],
            'features_text' => ['nullable', 'string', 'max:4000'],
            'feature_flags' => ['nullable', 'array'],
            'feature_flags.*' => ['nullable', Rule::in(['0', '1'])],
        ]);

        $features = collect(preg_split('/\r\n|\r|\n/', (string) ($data['features_text'] ?? '')))
            ->map(fn (?string $line): string => trim((string) $line))
            ->filter()
            ->values()
            ->all();

        $baseFlags = (array) ($this->planService->detail($normalizedPlan)['feature_flags'] ?? []);
        $submittedFlags = (array) ($data['feature_flags'] ?? []);
        $featureFlags = collect($baseFlags)
            ->mapWithKeys(fn ($value, $key) => [$key => (bool) ($submittedFlags[$key] ?? false)])
            ->all();

        $payload = [
            'plan_key' => $normalizedPlan,
            'name' => $this->nullableString($data['name'] ?? null),
            'price' => $this->nullableString($data['price'] ?? null),
            'promo_price' => $this->nullableString($data['promo_price'] ?? null),
            'promo_label' => $this->nullableString($data['promo_label'] ?? null),
            'promo_is_active' => $request->boolean('promo_is_active'),
            'promo_starts_at' => $data['promo_starts_at'] ?? null,
            'promo_ends_at' => $data['promo_ends_at'] ?? null,
            'billing_cycle' => $this->nullableString($data['billing_cycle'] ?? null),
            'booking_limit_total' => $data['booking_limit_total'] ?? null,
            'benefit' => $this->nullableString($data['benefit'] ?? null),
            'features' => empty($features) ? null : $features,
            'feature_flags' => empty($featureFlags) ? null : $featureFlags,
        ];

        PlanOverride::query()->updateOrCreate(
            ['plan_key' => $normalizedPlan],
            $payload
        );

        $this->auditLogService->log(
            action: 'plan_override_updated',
            targetType: PlanOverride::class,
            targetLabel: $normalizedPlan,
            meta: [
                'plan_key' => $normalizedPlan,
                'booking_limit_total' => $payload['booking_limit_total'],
                'promo_price' => $payload['promo_price'],
                'promo_is_active' => $payload['promo_is_active'],
                'promo_starts_at' => $payload['promo_starts_at'],
                'promo_ends_at' => $payload['promo_ends_at'],
            ],
            request: $request
        );

        return redirect()
            ->route('backend.plans.index')
            ->with('success', 'Pengaturan paket berhasil disimpan.');
    }

    public function reset(Request $request, string $planKey): RedirectResponse
    {
        $normalizedPlan = $this->planService->normalizePlan($planKey);
        abort_unless($this->planService->isKnownPlan($normalizedPlan), 404);

        PlanOverride::query()
            ->where('plan_key', $normalizedPlan)
            ->delete();

        $this->auditLogService->log(
            action: 'plan_override_reset',
            targetType: PlanOverride::class,
            targetLabel: $normalizedPlan,
            meta: [
                'plan_key' => $normalizedPlan,
            ],
            request: $request
        );

        return redirect()
            ->route('backend.plans.index')
            ->with('success', 'Override paket berhasil direset ke konfigurasi default.');
    }

    private function nullableString(?string $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
