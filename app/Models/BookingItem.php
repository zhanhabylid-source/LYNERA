<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'booking_id',
        'service_id',
        'people_count',
        'unit_price',
        'duration_minutes',
        'subtotal',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'booking_id' => 'integer',
        'service_id' => 'integer',
        'people_count' => 'integer',
        'unit_price' => 'decimal:2',
        'duration_minutes' => 'integer',
        'subtotal' => 'decimal:2',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }
}
