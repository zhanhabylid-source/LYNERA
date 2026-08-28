<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class NotifyExpiringSubscriptions extends Command
{
    protected $signature = 'subscriptions:notify-expiring {--days=7 : Ambang hari sebelum berakhir} {--dry-run : Hanya tampilkan target tanpa mengirim}';

    protected $description = 'Kirim pengingat email H-7 untuk langganan berbayar (Pro/Premium) yang akan berakhir.';

    public function __construct(
        private readonly NotificationService $notificationService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $now = now();
        $until = now()->addDays($days)->endOfDay();
        $paidPlans = [Subscription::PLAN_PRO, Subscription::PLAN_PREMIUM];

        $subscriptions = Subscription::query()
            ->with('user')
            ->whereIn('plan', $paidPlans)
            ->whereNotNull('expired_at')
            ->whereBetween('expired_at', [$now, $until])
            ->whereHas('user', function ($query): void {
                $query->where('role', '!=', 'super_admin')->where('is_suspended', false);
            })
            ->get();

        $sent = 0;
        $skipped = 0;

        foreach ($subscriptions as $subscription) {
            $tenant = $subscription->user;

            if ($tenant === null || ! is_string($tenant->email) || trim($tenant->email) === '') {
                $skipped++;
                continue;
            }

            // Cukup sekali per hari kalender agar tidak spam.
            if ($subscription->expiry_reminder_sent_at && $subscription->expiry_reminder_sent_at->isSameDay($now)) {
                $skipped++;
                continue;
            }

            $daysLeft = (int) ceil($now->diffInDays($subscription->expired_at, false));
            if ($daysLeft < 0) {
                $skipped++;
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line(sprintf('DRY-RUN: akan mengingatkan %s (sisa %d hari).', $tenant->email, $daysLeft));
                $sent++;
                continue;
            }

            $this->notificationService->sendSubscriptionExpiringEmail($tenant, $subscription, $daysLeft);
            $subscription->forceFill(['expiry_reminder_sent_at' => now()])->save();
            $sent++;
        }

        $this->info(sprintf('Selesai kirim pengingat kadaluarsa. Diproses: %d, dilewati: %d.', $sent, $skipped));

        return self::SUCCESS;
    }
}
