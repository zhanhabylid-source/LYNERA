<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TenantPaymentAccount extends Model
{
    use HasFactory;

    public const TYPE_BANK = 'bank';
    public const TYPE_EWALLET = 'ewallet';
    public const TYPE_QRIS = 'qris';

    protected $fillable = [
        'tenant_id',
        'type',
        'bank_name',
        'account_name',
        'account_number',
        'contact',
        'notes',
        'qr_code_path',
        'is_primary',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_EWALLET => 'E-Wallet',
            self::TYPE_QRIS => 'QRIS',
            default => 'Bank Transfer',
        };
    }

    public function qrCodeUrl(): ?string
    {
        if (! $this->qr_code_path || ! Storage::disk('public')->exists($this->qr_code_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->qr_code_path);
    }
}

