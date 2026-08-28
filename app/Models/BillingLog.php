<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'payment_id',
        'event_type',
        'amount',
        'metadata',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'payment_id' => 'integer',
        'amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }
}
