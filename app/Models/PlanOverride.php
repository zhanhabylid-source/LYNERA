<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_key',
        'name',
        'price',
        'promo_price',
        'promo_label',
        'promo_is_active',
        'promo_starts_at',
        'promo_ends_at',
        'billing_cycle',
        'booking_limit_total',
        'benefit',
        'features',
        'feature_flags',
    ];

    protected $casts = [
        'booking_limit_total' => 'integer',
        'promo_is_active' => 'boolean',
        'promo_starts_at' => 'datetime',
        'promo_ends_at' => 'datetime',
        'features' => 'array',
        'feature_flags' => 'array',
    ];
}

