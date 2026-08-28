<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\PlanService;
use App\Services\SubscriptionService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(PlanService $planService): View
    {
        $plan = $planService->normalizePlan(request()->query('plan', $planService->defaultPlan()));

        return view('auth.register', [
            'selectedPlan' => $plan,
            'availablePlans' => $planService->all(),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $planService = app(PlanService::class);
        $subscriptionService = app(SubscriptionService::class);
        $plan = $planService->normalizePlan($request->input('plan', $request->query('plan', $planService->defaultPlan())));

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'plan' => ['required', Rule::in($planService->allowedPlans())],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);
        $subscription = $subscriptionService->createTrialForUser($user->id, $plan, $planService->trialDays());
        app(NotificationService::class)->sendWelcomeMessage($user, $subscription);

        return redirect('/onboarding');
    }
}
