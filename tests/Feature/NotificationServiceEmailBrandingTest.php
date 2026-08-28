<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\PlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationServiceEmailBrandingTest extends TestCase
{
    use RefreshDatabase;

    private function makeBookingFor(User $tenant, string $suffix): Booking
    {
        $service = Service::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'tscheck-service-'.$suffix,
            'price' => 500000,
            'duration' => 90,
        ]);

        $customer = Customer::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'tscheck-customer-'.$suffix,
            'phone' => '081200000'.substr($suffix, -3),
            'email' => 'tscheck-customer-'.$suffix.'@example.com',
        ]);

        return Booking::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '10:00:00',
            'end_time' => '12:00:00',
            'status' => Booking::STATUS_PENDING,
        ]);
    }

    public function test_public_booking_alert_sends_lynera_branded_email_and_returns_true(): void
    {
        Mail::fake();

        $suffix = 'pub-'.uniqid();
        $tenant = User::factory()->create(['email' => 'tscheck-mua-'.$suffix.'@example.com']);
        $booking = $this->makeBookingFor($tenant, $suffix);

        $service = $this->app->make(NotificationService::class);
        $result = $service->sendPublicBookingAlert($tenant, $booking);

        $this->assertTrue($result);
    }

    public function test_public_booking_alert_email_body_contains_lynera_branding_and_dashboard_button(): void
    {
        $suffix = 'pub-body-'.uniqid();
        $tenant = User::factory()->create(['email' => 'tscheck-mua-'.$suffix.'@example.com']);
        $booking = $this->makeBookingFor($tenant, $suffix);

        $view = view('emails.notification', [
            'preheader' => 'test',
            'badge' => 'Booking Baru',
            'heading' => 'Ada Booking Baru Masuk!',
            'intro' => 'Halo test',
            'details' => ['Pelanggan' => $booking->customer->name],
            'actionLabel' => 'Buka Dashboard Booking',
            'actionUrl' => route('admin.bookings.index'),
            'outro' => 'outro',
        ])->render();

        $this->assertStringContainsString('LYNERA', $view);
        $this->assertStringContainsString('Buka Dashboard Booking', $view);
        $this->assertStringContainsString(route('admin.bookings.index'), $view);
        $this->assertStringContainsString('pavicon.png', $view);
    }

    public function test_tomorrow_reminder_email_uses_lynera_branding_and_calendar_button(): void
    {
        $suffix = 'h1-'.uniqid();
        $tenant = User::factory()->create(['email' => 'tscheck-mua-'.$suffix.'@example.com']);
        $booking = $this->makeBookingFor($tenant, $suffix);

        $service = $this->app->make(NotificationService::class);
        $result = $service->sendTomorrowBookingReminderEmail($tenant, $booking);

        $this->assertTrue($result);

        $view = view('emails.notification', [
            'preheader' => 'test',
            'badge' => 'Pengingat',
            'heading' => 'Pengingat: Ada Booking Besok',
            'intro' => 'Halo test',
            'details' => ['Pelanggan' => $booking->customer->name],
            'actionLabel' => 'Lihat Kalender',
            'actionUrl' => route('admin.calendar.index'),
            'outro' => 'outro',
        ])->render();

        $this->assertStringContainsString('LYNERA', $view);
        $this->assertStringContainsString('Lihat Kalender', $view);
        $this->assertStringContainsString(route('admin.calendar.index'), $view);
    }

    public function test_verification_and_reset_password_mail_use_lynera_branded_template(): void
    {
        $suffix = 'auth-'.uniqid();
        $user = User::factory()->create(['email' => 'tscheck-auth-'.$suffix.'@example.com']);

        $verification = (new \Illuminate\Auth\Notifications\VerifyEmail())->toMail($user);
        $rendered = $verification->render();
        $this->assertStringContainsString('LYNERA', $rendered);
        $this->assertStringContainsString('Verifikasi Email', $rendered);

        $reset = (new \Illuminate\Auth\Notifications\ResetPassword('tscheck-token'))->toMail($user);
        $renderedReset = $reset->render();
        $this->assertStringContainsString('LYNERA', $renderedReset);
        $this->assertStringContainsString('Atur Ulang Password', $renderedReset);
        $this->assertStringContainsString('Jangan bagikan tautan reset password', $renderedReset);
    }
}
