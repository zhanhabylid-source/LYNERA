<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';

    public const METHOD_MANUAL = 'manual';
    public const METHOD_MIDTRANS = 'midtrans';

    protected $fillable = [
        'tenant_id',
        'booking_id',
        'amount',
        'discount_amount',
        'dp_amount',
        'paid_amount',
        'status',
        'payment_method',
        'dp_paid_at',
        'paid_at',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'booking_id' => 'integer',
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'dp_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'dp_paid_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }

    public function isDpPaid(): bool
    {
        return $this->dp_paid_at !== null || (float) $this->paid_amount >= (float) $this->dp_amount;
    }

    public function isSettled(): bool
    {
        return $this->status === self::STATUS_PAID && (float) $this->paid_amount >= (float) $this->amount;
    }
}
