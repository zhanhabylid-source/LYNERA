<?php

namespace App\Repositories;

use App\Models\Subscription;

class SubscriptionRepository
{
    public function firstOrCreateForUser(int $userId): Subscription
    {
        return Subscription::query()->firstOrCreate(
            ['user_id' => $userId],
            ['plan' => Subscription::PLAN_FREE, 'expired_at' => null]
        );
    }

    public function findByUserId(int $userId): ?Subscription
    {
        return Subscription::query()->where('user_id', $userId)->first();
    }

    public function update(Subscription $subscription, array $data): Subscription
    {
        $subscription->update($data);

        return $subscription->refresh();
    }
}
