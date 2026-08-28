<?php

namespace App\Services;

use App\Models\PublicBookingForm;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class PublicBookingFormService
{
    public function listForTenant(int $tenantId): Collection
    {
        return PublicBookingForm::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->latest()
            ->get();
    }

    public function createForTenant(
        int $tenantId,
        array $serviceIds,
        ?int $maxSubmissions = null,
        ?float $transportFee = null,
        string $termsTitle = 'Syarat & Ketentuan Booking',
        string $termsContent = ''
    ): PublicBookingForm
    {
        $normalizedTransportFee = round(max(0, (float) ($transportFee ?? 0)), 2);

        return PublicBookingForm::withoutGlobalScopes()->create([
            'tenant_id' => $tenantId,
            'token' => Str::random(48),
            'expires_at' => now()->addHours(48),
            'is_active' => true,
            'settings' => [
                'service_ids' => array_values(array_unique($serviceIds)),
                'transport_fee' => $normalizedTransportFee,
                'terms' => [
                    'title' => trim($termsTitle),
                    'content' => trim($termsContent),
                ],
            ],
            'max_submissions' => $maxSubmissions,
            'submission_count' => 0,
        ]);
    }

    public function findAccessibleByToken(string $token): ?PublicBookingForm
    {
        $form = PublicBookingForm::withoutGlobalScopes()
            ->where('token', $token)
            ->first();

        if ($form === null) {
            return null;
        }

        if (! $form->isAccessible()) {
            return null;
        }

        return $form;
    }

    public function findByToken(string $token): ?PublicBookingForm
    {
        return PublicBookingForm::withoutGlobalScopes()
            ->where('token', $token)
            ->first();
    }

    public function incrementSubmission(PublicBookingForm $form): PublicBookingForm
    {
        $form->submission_count++;
        if ($form->max_submissions !== null && $form->submission_count >= $form->max_submissions) {
            $form->is_active = false;
        }
        $form->save();

        return $form->refresh();
    }

    public function deactivate(PublicBookingForm $form): void
    {
        $form->is_active = false;
        $form->save();
    }

    public function extendBy48Hours(PublicBookingForm $form): PublicBookingForm
    {
        $base = $form->expires_at->isFuture() ? $form->expires_at : now();
        $form->expires_at = $base->copy()->addHours(48);
        $form->is_active = true;
        $form->save();

        return $form->refresh();
    }

    public function getAllowedServiceIds(PublicBookingForm $form): array
    {
        $ids = $form->settings['service_ids'] ?? [];

        return array_values(array_map('intval', array_filter($ids, fn ($id) => is_numeric($id))));
    }

    public function isExpired(PublicBookingForm $form): bool
    {
        return Carbon::parse($form->expires_at)->isPast();
    }

    public function resolveTerms(PublicBookingForm $form, ?User $tenant = null): array
    {
        $settings = is_array($form->settings) ? $form->settings : [];
        $terms = is_array($settings['terms'] ?? null) ? $settings['terms'] : [];

        $titleFromForm = trim((string) ($terms['title'] ?? ''));
        $contentFromForm = trim((string) ($terms['content'] ?? ''));

        $titleFromTenant = trim((string) ($tenant?->booking_terms_title ?? ''));
        $contentFromTenant = trim((string) ($tenant?->booking_terms_content ?? ''));

        $title = $titleFromForm !== '' ? $titleFromForm : ($titleFromTenant !== '' ? $titleFromTenant : 'Syarat & Ketentuan Booking');
        $content = $contentFromForm !== '' ? $contentFromForm : $contentFromTenant;

        if ($content === '') {
            $content = 'Dengan melanjutkan, Anda menyetujui kebijakan booking dari MUA terkait DP, penjadwalan ulang, dan pembatalan.';
        }

        return [
            'title' => $title,
            'content' => $content,
        ];
    }

    public function syncTermsToActiveForms(int $tenantId, string $title, string $content): int
    {
        $forms = PublicBookingForm::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        $updated = 0;
        foreach ($forms as $form) {
            $settings = is_array($form->settings) ? $form->settings : [];
            $settings['terms'] = [
                'title' => trim($title),
                'content' => trim($content),
            ];

            $form->settings = $settings;
            $form->save();
            $updated++;
        }

        return $updated;
    }

    public function resolveTransportFee(PublicBookingForm $form, ?User $tenant = null): float
    {
        $settings = is_array($form->settings) ? $form->settings : [];
        $fromForm = $settings['transport_fee'] ?? null;
        if (is_numeric($fromForm)) {
            return round(max(0, (float) $fromForm), 2);
        }

        return 0.0;
    }
}
