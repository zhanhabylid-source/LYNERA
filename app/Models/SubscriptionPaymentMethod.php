<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SubscriptionPaymentMethod extends Model
{
    use HasFactory;

    public const TYPE_BANK = 'bank';
    public const TYPE_EWALLET = 'ewallet';
    public const TYPE_QRIS = 'qris';

    protected $fillable = [
        'type',
        'provider_name',
        'account_name',
        'account_number',
        'contact',
        'instructions',
        'qr_code_path',
        'is_active',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_BANK => 'Bank Transfer',
            self::TYPE_EWALLET => 'E-Wallet',
            self::TYPE_QRIS => 'QRIS',
            default => strtoupper((string) $this->type),
        };
    }

    public function displayLabel(): string
    {
        return $this->typeLabel().' - '.$this->provider_name;
    }

    public function qrCodeUrl(): ?string
    {
        if (! $this->qr_code_path || ! Storage::disk('public')->exists($this->qr_code_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->qr_code_path);
    }
}