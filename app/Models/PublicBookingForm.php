<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicBookingForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'token',
        'expires_at',
        'is_active',
        'settings',
        'max_submissions',
        'submission_count',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'settings' => 'array',
        'max_submissions' => 'integer',
        'submission_count' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isAccessible(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->isExpired()) {
            return false;
        }

        if ($this->max_submissions !== null && $this->submission_count >= $this->max_submissions) {
            return false;
        }

        return true;
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }
}
