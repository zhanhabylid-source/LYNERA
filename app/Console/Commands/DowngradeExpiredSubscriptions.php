<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class DowngradeExpiredSubscriptions extends Command
{
    protected $signature = 'subscriptions:downgrade-expired';

    protected $description = 'Turunkan tenant ke paket Free otomatis jika masa berlaku berbayar (Pro/Premium) sudah habis.';

    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $subscriptions = Subscription::query()
            ->whereIn('plan', [Subscription::PLAN_PRO, Subscription::PLAN_PREMIUM])
            ->whereNotNull('expired_at')
            ->where('expired_at', '<', now())
            ->get();

        $downgraded = 0;

        foreach ($subscriptions as $subscription) {
            if ($this->subscriptionService->downgradeIfExpired((int) $subscription->user_id)) {
                $downgraded++;
            }
        }

        $this->info(sprintf('Selesai. Tenant diturunkan ke Free: %d.', $downgraded));

        return self::SUCCESS;
    }
}
