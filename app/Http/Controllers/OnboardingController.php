<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\StoreServiceRequest;
use App\Models\User;
use App\Services\BookingService;
use App\Services\CustomerService;
use App\Services\PlanService;
use App\Services\ServiceService;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class OnboardingController extends Controller
{
    public function __construct(
        private readonly ServiceService $serviceService,
        private readonly BookingService $bookingService,
        private readonly CustomerService $customerService,
        private readonly PlanService $planService,
        private readonly SubscriptionService $subscriptionService
    ) {
    }

    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $this->subscriptionService->ensureUserSubscription((int) $user->id);
        $user = $user->refresh();

        if ($user->onboarding_completed_at === null && $user->hasCompletedOnboarding()) {
            $user->markOnboardingCompleted();
            $user = $user->refresh();
        }

        // Tenant yang sudah menyelesaikan onboarding langsung diarahkan ke dashboard.
        if ($user->onboarding_completed_at !== null) {
            return redirect()->route('admin.dashboard');
        }

        $servicesCount = $user->services()->count();
        $customersCount = $user->customers()->count();
        $bookingsCount = $user->bookings()->count();
        $initialStep = $this->resolveInitialStep($request, $servicesCount, $customersCount, $bookingsCount);
        $plan = $this->planService->normalizePlan(getUserPlan($user));
        $trialDaysLeft = getRemainingDays($user);
        $planDetail = $this->planService->detail($plan);
        $bookingUsage = $this->subscriptionService->getBookingUsage((int) $user->id);

        return view('onboarding', [
            'plan' => $plan,
            'planBenefit' => (string) ($planDetail['benefit'] ?? ''),
            'planLimitLabel' => (string) ($planDetail['booking_limit_label'] ?? ''),
            'trialDaysLeft' => $trialDaysLeft,
            'trialExpiry' => $user->subscription?->expired_at,
            'bookingUsage' => $bookingUsage,
            'servicesCount' => $servicesCount,
            'customersCount' => $customersCount,
            'bookingsCount' => $bookingsCount,
            'isCompleted' => $user->onboarding_completed_at !== null,
            'initialStep' => $initialStep,
            'services' => $user->services()->orderBy('name')->get(['id', 'name']),
            'customers' => $user->customers()->orderBy('name')->get(['id', 'name', 'phone']),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'studio_name' => ['nullable', 'string', 'max:255'],
            'studio_location' => ['nullable', 'string', 'max:255'],
            'studio_maps_link' => ['nullable', 'url', 'max:255'],
        ]);

        $request->user()->update($validated);

        $this->syncCompletionStatus($request->user());

        return back()
            ->with('success', 'Profil berhasil diperbarui.')
            ->with('onboarding_step', 2);
    }

    public function storeFirstService(StoreServiceRequest $request): RedirectResponse
    {
        $this->serviceService->create($request->validated());

        $this->syncCompletionStatus($request->user());

        return back()
            ->with('success', 'Layanan pertama berhasil dibuat.')
            ->with('onboarding_step', 3);
    }

    public function storeFirstCustomer(StoreCustomerRequest $request): RedirectResponse
    {
        $this->customerService->create($request->validated());
        $this->syncCompletionStatus($request->user());

        return back()
            ->with('success', 'Pelanggan pertama berhasil dibuat.')
            ->with('onboarding_step', 4);
    }

    public function storeFirstBooking(StoreBookingRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $this->bookingService->create($validated);
        } catch (ValidationException $exception) {
            return back()
                ->withInput($validated)
                ->with('onboarding_step', 4)
                ->withErrors($exception->errors());
        } catch (InvalidArgumentException $exception) {
            $message = $exception->getMessage();
            $input = $validated;

            $normalizedMessage = strtolower($message);
            if (str_contains($normalizedMessage, 'overlaps') || str_contains($normalizedMessage, 'bentrok')) {
                $suggested = $this->suggestNextAvailableSlot($validated);
                if ($suggested !== null) {
                    $input['booking_date'] = $suggested['date'];
                    $input['booking_time'] = $suggested['time'];
                    $message .= sprintf(
                        ' Saran jadwal berikutnya: %s pukul %s.',
                        Carbon::parse($suggested['date'])->format('d M Y'),
                        substr($suggested['time'], 0, 5)
                    );
                }
            }

            return back()
                ->withInput($input)
                ->with('onboarding_step', 4)
                ->withErrors(['booking_time' => $message]);
        }

        $this->syncCompletionStatus($request->user()->refresh());

        return redirect()->route('admin.calendar.index')->with('success', 'Onboarding selesai. Berikutnya, cek jadwal layanan Anda.');
    }

    private function syncCompletionStatus(User $user): void
    {
        $freshUser = $user->refresh();
        if ($freshUser->hasCompletedOnboarding()) {
            $freshUser->markOnboardingCompleted();
        }
    }

    private function resolveInitialStep(
        Request $request,
        int $servicesCount,
        int $customersCount,
        int $bookingsCount
    ): int {
        $sessionStep = (int) $request->session()->get('onboarding_step', 0);
        if ($sessionStep >= 1 && $sessionStep <= 4) {
            return $sessionStep;
        }

        if ($request->old('customer_id') || $request->old('service_id') || $request->old('booking_date') || $request->old('booking_time')) {
            return 4;
        }
        if ($request->old('phone') || $request->old('instagram')) {
            return 3;
        }
        if ($request->old('price') || $request->old('duration')) {
            return 2;
        }
        if ($request->old('studio_name') || $request->old('studio_location') || $request->old('studio_maps_link')) {
            return 1;
        }

        if ($bookingsCount > 0) {
            return 4;
        }
        if ($customersCount === 0) {
            return 3;
        }
        if ($servicesCount === 0) {
            return 2;
        }

        return 4;
    }

    private function suggestNextAvailableSlot(array $data): ?array
    {
        $serviceId = (int) ($data['service_id'] ?? 0);
        $date = (string) ($data['booking_date'] ?? '');
        $time = (string) ($data['booking_time'] ?? '');

        if ($serviceId <= 0 || $date === '' || $time === '') {
            return null;
        }

        try {
            $service = $this->serviceService->findOrFail($serviceId);
            $duration = max(1, (int) $service->duration);
            $start = Carbon::parse($date.' '.$time);
        } catch (\Throwable) {
            return null;
        }

        if ($start->isPast()) {
            $start = now()->addMinutes(30)->second(0);
        }

        for ($i = 0; $i < 48; $i++) {
            $candidateStart = $start->copy()->addMinutes($i * 30);
            $candidateEnd = $candidateStart->copy()->addMinutes($duration);

            try {
                $this->bookingService->checkAvailability(
                    $candidateStart->toDateString(),
                    $candidateStart->format('H:i:s'),
                    $candidateEnd->format('H:i:s')
                );

                return [
                    'date' => $candidateStart->toDateString(),
                    'time' => $candidateStart->format('H:i'),
                ];
            } catch (InvalidArgumentException) {
                continue;
            }
        }

        return null;
    }
}
