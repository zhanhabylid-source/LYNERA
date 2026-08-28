<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\PlanService;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly PlanService $planService,
        private readonly SubscriptionService $subscriptionService
    ) {
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user()
            ->loadCount(['services', 'customers', 'bookings'])
            ->load('paymentAccounts');
        $planKey = $this->planService->normalizePlan(getUserPlan($user));
        $bookingUsage = $this->subscriptionService->getBookingUsage((int) $user->id);

        return view('profile.edit', [
            'user' => $user,
            'planKey' => $planKey,
            'planDetail' => $this->planService->detail($planKey),
            'bookingUsage' => $bookingUsage,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $removeLogo = (bool) ($validated['remove_logo'] ?? false);
        unset(
            $validated['logo'],
            $validated['remove_logo']
        );

        DB::transaction(function () use (
            $request,
            $user,
            $validated,
            $removeLogo
        ): void {
            $user->fill($validated);

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            if ($removeLogo && $user->logo_path) {
                Storage::disk('public')->delete($user->logo_path);
                $user->logo_path = null;
            }

            if ($request->hasFile('logo')) {
                if ($user->logo_path) {
                    Storage::disk('public')->delete($user->logo_path);
                }

                $user->logo_path = $request->file('logo')->store(
                    'tenants/'.$user->id.'/branding',
                    'public'
                );
            }

            $user->save();
        });

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        if ($user->logo_path) {
            Storage::disk('public')->delete($user->logo_path);
        }

        $user->paymentAccounts()->delete();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
