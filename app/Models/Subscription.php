<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    public const PLAN_FREE = 'free';
    public const PLAN_PRO = 'pro';
    public const PLAN_PREMIUM = 'premium';

    protected $fillable = [
        'user_id',
        'plan',
        'expired_at',
        'expiry_reminder_sent_at',
        'bookings_consumed_total',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'expired_at' => 'datetime',
        'expiry_reminder_sent_at' => 'datetime',
        'bookings_consumed_total' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
