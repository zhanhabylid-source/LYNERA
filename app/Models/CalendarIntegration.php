<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarIntegration extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'google_event_id',
    ];

    protected $casts = [
        'booking_id' => 'integer',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
