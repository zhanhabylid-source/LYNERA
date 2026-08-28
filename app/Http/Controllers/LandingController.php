<?php

namespace App\Http\Controllers;

use App\Services\PlanService;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function __construct(
        private readonly PlanService $planService
    ) {
    }

    public function home(): View
    {
        return view('landing.home');
    }

    public function pricing(): View
    {
        $plans = collect($this->planService->all())
            ->map(fn (array $detail, string $key) => $this->planService->detail($key))
            ->all();

        return view('landing.pricing', [
            'plans' => $plans,
            'defaultPlan' => $this->planService->defaultPlan(),
        ]);
    }

    public function features(): View
    {
        return view('landing.features');
    }
}
