<?php

namespace App\Models;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_suspended',
        'suspended_at',
        'suspended_reason',
        'studio_name',
        'studio_location',
        'studio_maps_link',
        'default_transport_fee',
        'logo_path',
        'payment_bank_name',
        'payment_account_name',
        'payment_account_number',
        'payment_contact',
        'payment_instructions',
        'notify_tomorrow_booking',
        'booking_terms_title',
        'booking_terms_content',
        'booking_terms_updated_at',
        'plan_activation_notice_until',
        'plan_activation_notice_plan',
        'onboarding_completed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_suspended' => 'boolean',
            'suspended_at' => 'datetime',
            'notify_tomorrow_booking' => 'boolean',
            'default_transport_fee' => 'decimal:2',
            'booking_terms_updated_at' => 'datetime',
            'plan_activation_notice_until' => 'datetime',
            'onboarding_completed_at' => 'datetime',
        ];
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'tenant_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'tenant_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'tenant_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'tenant_id');
    }

    public function publicBookingForms(): HasMany
    {
        return $this->hasMany(PublicBookingForm::class, 'tenant_id');
    }

    public function paymentAccounts(): HasMany
    {
        return $this->hasMany(TenantPaymentAccount::class, 'tenant_id')
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function subscriptionUpgradeRequests(): HasMany
    {
        return $this->hasMany(SubscriptionUpgradeRequest::class, 'tenant_id');
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function googleCalendarToken(): HasOne
    {
        return $this->hasOne(GoogleCalendarToken::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification((string) $token));
    }

    public function isSuspended(): bool
    {
        return (bool) $this->is_suspended;
    }

    public function hasCompletedOnboarding(): bool
    {
        if ($this->onboarding_completed_at !== null) {
            return true;
        }

        return $this->services()->exists()
            && $this->customers()->exists()
            && $this->bookings()->exists();
    }

    public function markOnboardingCompleted(): void
    {
        if ($this->onboarding_completed_at !== null) {
            return;
        }

        $this->forceFill([
            'onboarding_completed_at' => now(),
        ])->save();
    }

    public function logoUrl(): ?string
    {
        $path = trim((string) $this->logo_path);
        if ($path === '') {
            return null;
        }

        // Strip any leftover "public/" prefix (defensive — Storage::url handles it too).
        $relative = ltrim(Str::replaceFirst('public/', '', $path), '/');
        $disk = Storage::disk('public');

        if (! $disk->exists($relative)) {
            return null;
        }

        // Cache-buster keyed off the file's mtime so browsers refresh after re-upload.
        try {
            $mtime = $disk->lastModified($relative);
        } catch (\Throwable $e) {
            $mtime = null;
        }

        $url = $disk->url($relative); // Menghormati APP_URL & disk config
        return $mtime ? $url.'?v='.$mtime : $url;
    }

    public function primaryPaymentAccount(): ?TenantPaymentAccount
    {
        if ($this->relationLoaded('paymentAccounts')) {
            /** @var Collection<int, TenantPaymentAccount> $accounts */
            $accounts = $this->getRelation('paymentAccounts');

            return $accounts->first();
        }

        return $this->paymentAccounts()->first();
    }
}
