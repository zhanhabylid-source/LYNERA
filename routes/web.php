<?php

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PublicBookingFormController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Backend\AuditLogController;
use App\Http\Controllers\Backend\DashboardController as BackendDashboardController;
use App\Http\Controllers\Backend\PlanManagementController;
use App\Http\Controllers\Backend\SubscriptionPaymentMethodController;
use App\Http\Controllers\Backend\TenantManagementController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfilePaymentMethodController;
use App\Http\Controllers\GoogleCalendarAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check() && auth()->user()->isSuperAdmin()) {
        return redirect()->route('backend.dashboard');
    }

    return redirect()->route('onboarding.index');
});
Route::get('/welcome', [LandingController::class, 'home'])->name('landing.home');
Route::get('/pricing', [LandingController::class, 'pricing'])->name('landing.pricing');
Route::get('/features', [LandingController::class, 'features'])->name('landing.features');

Route::get('/dashboard', function () {
    if (auth()->user()?->isSuperAdmin()) {
        return redirect()->route('backend.dashboard');
    }

    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'not_suspended', 'verified', 'subscription'])->name('dashboard');

Route::middleware(['auth', 'not_suspended', 'subscription'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
    Route::post('/calendar/events', [CalendarController::class, 'store'])->name('calendar.store');
    Route::patch('/calendar/events/{booking}/reschedule', [CalendarController::class, 'reschedule'])->name('calendar.reschedule');
    Route::resource('services', ServiceController::class)->except('show');
    Route::resource('customers', CustomerController::class)->except('show');
    Route::get('/bookings/{booking}/invoice', [BookingController::class, 'invoice'])->name('bookings.invoice');
    Route::get('/bookings/{booking}/invoice/preview', [BookingController::class, 'invoicePreview'])->name('bookings.invoice.preview');
    Route::post('/bookings/{booking}/pay-now', [BookingController::class, 'payNow'])->name('bookings.pay-now');
    Route::post('/bookings/{booking}/reschedule', [BookingController::class, 'reschedule'])->name('bookings.reschedule');
    Route::patch('/bookings/{booking}/confirm', [BookingController::class, 'confirm'])->name('bookings.confirm');
    Route::patch('/bookings/terms', [BookingController::class, 'updateTerms'])->name('bookings.terms.update');
    Route::get('/booking-links', [PublicBookingFormController::class, 'index'])->name('booking-links.index');
    Route::post('/booking-links', [PublicBookingFormController::class, 'store'])->name('booking-links.store');
    Route::patch('/booking-links/{publicBookingForm}/extend', [PublicBookingFormController::class, 'extend'])->name('booking-links.extend');
    Route::patch('/booking-links/{publicBookingForm}/deactivate', [PublicBookingFormController::class, 'deactivate'])->name('booking-links.deactivate');
    Route::resource('bookings', BookingController::class);

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::patch('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
    Route::patch('/payments/{payment}/pricing', [PaymentController::class, 'updatePricing'])->name('payments.update-pricing');
    Route::patch('/payments/{payment}/mark-dp-paid', [PaymentController::class, 'markDpPaid'])->name('payments.mark-dp-paid');
    Route::patch('/payments/{payment}/mark-settled', [PaymentController::class, 'markSettled'])->name('payments.mark-settled');
    Route::patch('/payments/{payment}/cancel-booking', [PaymentController::class, 'cancelBooking'])->name('payments.cancel-booking');
    Route::patch('/payments/{payment}/mark-paid', [PaymentController::class, 'markAsPaid'])->name('payments.mark-paid');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

});

Route::middleware(['auth', 'not_suspended'])->group(function () {
    Route::get('/google/redirect', [GoogleCalendarAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/google/callback', [GoogleCalendarAuthController::class, 'callback'])->name('google.callback');

    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');
    Route::post('/onboarding/profile', [OnboardingController::class, 'updateProfile'])->name('onboarding.profile');
    Route::post('/onboarding/service', [OnboardingController::class, 'storeFirstService'])->name('onboarding.service');
    Route::post('/onboarding/customer', [OnboardingController::class, 'storeFirstCustomer'])->name('onboarding.customer');
    Route::post('/onboarding/booking', [OnboardingController::class, 'storeFirstBooking'])->name('onboarding.booking');
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/upgrade-request', [BillingController::class, 'requestUpgrade'])->name('billing.upgrade-request');
    Route::patch('/billing/upgrade-request/{upgradeRequest}/confirm-payment', [BillingController::class, 'confirmUpgradePayment'])->name('billing.upgrade-request.confirm-payment');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/payment-methods', [ProfilePaymentMethodController::class, 'store'])->name('profile.payment-methods.store');
    Route::put('/profile/payment-methods/{paymentMethod}', [ProfilePaymentMethodController::class, 'update'])->name('profile.payment-methods.update');
    Route::patch('/profile/payment-methods/{paymentMethod}/toggle', [ProfilePaymentMethodController::class, 'toggle'])->name('profile.payment-methods.toggle');
    Route::patch('/profile/payment-methods/{paymentMethod}/primary', [ProfilePaymentMethodController::class, 'primary'])->name('profile.payment-methods.primary');
    Route::delete('/profile/payment-methods/{paymentMethod}', [ProfilePaymentMethodController::class, 'destroy'])->name('profile.payment-methods.destroy');
});

Route::middleware('throttle:12,1')->group(function () {
    Route::get('/book/{token}', [PublicBookingController::class, 'show'])->name('public.booking.show');
    Route::post('/book/{token}', [PublicBookingController::class, 'store'])->name('public.booking.store');
    Route::get('/book/{token}/thank-you', [PublicBookingController::class, 'thankYou'])->name('public.booking.thank-you');
});

Route::middleware(['auth', 'not_suspended', 'super_admin'])
    ->prefix('backend')
    ->name('backend.')
    ->group(function () {
        Route::get('/', [BackendDashboardController::class, 'index'])->name('dashboard');
        Route::get('/tenants', [TenantManagementController::class, 'index'])->name('tenants.index');
        Route::get('/tenants/create', [TenantManagementController::class, 'create'])->name('tenants.create');
        Route::post('/tenants', [TenantManagementController::class, 'store'])->name('tenants.store');
        Route::get('/tenants/{tenant}/edit', [TenantManagementController::class, 'edit'])->name('tenants.edit');
        Route::put('/tenants/{tenant}', [TenantManagementController::class, 'update'])->name('tenants.update');
        Route::delete('/tenants/{tenant}', [TenantManagementController::class, 'destroy'])->name('tenants.destroy');
        Route::patch('/tenants/{tenant}/subscription', [TenantManagementController::class, 'updateSubscription'])->name('tenants.subscription.update');
        Route::patch('/tenants/{tenant}/quick-update', [TenantManagementController::class, 'quickUpdate'])->name('tenants.quick-update');
        Route::patch('/tenants/{tenant}/role', [TenantManagementController::class, 'updateRole'])->name('tenants.role.update');
        Route::patch('/tenants/{tenant}/suspend', [TenantManagementController::class, 'updateSuspend'])->name('tenants.suspend.update');
        Route::patch('/tenants/{tenant}/password-reset', [TenantManagementController::class, 'resetPassword'])->name('tenants.password-reset');
        Route::patch('/tenants/{tenant}/upgrade-requests/{upgradeRequest}/approve', [TenantManagementController::class, 'approveUpgradeRequest'])->name('tenants.upgrade-requests.approve');
        Route::patch('/tenants/{tenant}/upgrade-requests/{upgradeRequest}/reject', [TenantManagementController::class, 'rejectUpgradeRequest'])->name('tenants.upgrade-requests.reject');
        Route::get('/plans', [PlanManagementController::class, 'index'])->name('plans.index');
        Route::put('/plans/{planKey}', [PlanManagementController::class, 'update'])->name('plans.update');
        Route::delete('/plans/{planKey}', [PlanManagementController::class, 'reset'])->name('plans.reset');
        Route::get('/payment-methods', [SubscriptionPaymentMethodController::class, 'index'])->name('payment-methods.index');
        Route::post('/payment-methods', [SubscriptionPaymentMethodController::class, 'store'])->name('payment-methods.store');
        Route::put('/payment-methods/{paymentMethod}', [SubscriptionPaymentMethodController::class, 'update'])->name('payment-methods.update');
        Route::patch('/payment-methods/{paymentMethod}/toggle', [SubscriptionPaymentMethodController::class, 'toggle'])->name('payment-methods.toggle');
        Route::patch('/payment-methods/{paymentMethod}/primary', [SubscriptionPaymentMethodController::class, 'primary'])->name('payment-methods.primary');
        Route::delete('/payment-methods/{paymentMethod}', [SubscriptionPaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/backup', [\App\Http\Controllers\Backend\BackupController::class, 'index'])->name('backup.index');
        Route::get('/backup/download', [\App\Http\Controllers\Backend\BackupController::class, 'download'])->name('backup.download');
        Route::get('/backup/download-zip', [\App\Http\Controllers\Backend\BackupController::class, 'downloadZip'])->name('backup.download-zip');
        Route::get('/backup/stored/{filename}/download', [\App\Http\Controllers\Backend\BackupController::class, 'downloadStored'])->name('backup.stored.download')->where('filename', '[A-Za-z0-9_.\-]+');
        Route::delete('/backup/stored/{filename}', [\App\Http\Controllers\Backend\BackupController::class, 'destroyStored'])->name('backup.stored.destroy')->where('filename', '[A-Za-z0-9_.\-]+');
        Route::post('/backup/restore', [\App\Http\Controllers\Backend\BackupController::class, 'restore'])->name('backup.restore');
    });

require __DIR__.'/auth.php';
