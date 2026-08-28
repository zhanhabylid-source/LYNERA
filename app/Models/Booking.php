<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELED = 'canceled';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'service_id',
        'total_people',
        'booking_date',
        'booking_time',
        'end_time',
        'google_event_id',
        'tomorrow_reminder_sent_at',
        'terms_accepted_at',
        'terms_version',
        'terms_snapshot',
        'terms_acceptance_ip',
        'terms_acceptance_user_agent',
        'location',
        'transport_fee',
        'status',
        'notes',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'customer_id' => 'integer',
        'service_id' => 'integer',
        'total_people' => 'integer',
        'booking_date' => 'date',
        'booking_time' => 'string',
        'end_time' => 'string',
        'google_event_id' => 'string',
        'tomorrow_reminder_sent_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
        'transport_fee' => 'decimal:2',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function calendarIntegration(): HasOne
    {
        return $this->hasOne(CalendarIntegration::class);
    }

    public function bookingItems(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }

    public function serviceStartsAt(): ?Carbon
    {
        if ($this->booking_date === null) {
            return null;
        }

        $time = $this->booking_time ?? '00:00:00';

        return Carbon::parse($this->booking_date->format('Y-m-d').' '.$time);
    }

    public function serviceEndsAt(): ?Carbon
    {
        if ($this->booking_date === null) {
            return null;
        }

        $time = $this->end_time ?? $this->booking_time ?? '00:00:00';

        return Carbon::parse($this->booking_date->format('Y-m-d').' '.$time);
    }

    public function hasServicePassed(): bool
    {
        $endAt = $this->serviceEndsAt() ?? $this->serviceStartsAt();
        if ($endAt === null) {
            return false;
        }

        return $endAt->isPast();
    }
}
