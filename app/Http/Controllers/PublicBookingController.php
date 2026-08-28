<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitPublicBookingRequest;
use App\Models\Service;
use App\Models\User;
use App\Services\PublicBookingFormService;
use App\Services\PublicBookingSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use InvalidArgumentException;

class PublicBookingController extends Controller
{
    public function __construct(
        private readonly PublicBookingFormService $publicBookingFormService,
        private readonly PublicBookingSubmissionService $publicBookingSubmissionService
    ) {
    }

    public function show(string $token): View
    {
        $form = $this->publicBookingFormService->findByToken($token);
        if ($form === null || ! $form->isAccessible()) {
            return view('public-booking.expired');
        }

        $services = Service::withoutGlobalScopes()
            ->where('tenant_id', $form->tenant_id)
            ->whereIn('id', $this->publicBookingFormService->getAllowedServiceIds($form))
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'duration']);

        if ($services->isEmpty()) {
            return view('public-booking.expired', [
                'message' => 'No services are available for this booking link.',
            ]);
        }

        $tenant = User::query()->find($form->tenant_id);
        $effectiveTerms = $this->publicBookingFormService->resolveTerms($form, $tenant);
        $defaultTransportFee = $this->publicBookingFormService->resolveTransportFee($form, $tenant);

        return view('public-booking.form', [
            'form' => $form,
            'services' => $services,
            'token' => $token,
            'tenant' => $tenant,
            'effectiveTerms' => $effectiveTerms,
            'defaultTransportFee' => $defaultTransportFee,
        ]);
    }

    public function store(SubmitPublicBookingRequest $request, string $token): RedirectResponse
    {
        try {
            $payload = $request->validated();
            $payload['terms_accepted_at'] = now();
            $payload['terms_acceptance_ip'] = $request->ip();
            $payload['terms_acceptance_user_agent'] = (string) ($request->userAgent() ?? '');

            $this->publicBookingSubmissionService->submit($token, $payload);
        } catch (InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->withErrors(['booking' => $exception->getMessage()]);
        }

        return redirect()
            ->route('public.booking.thank-you', $token)
            ->with('submitted_phone', (string) ($payload['phone'] ?? ''));
    }

    public function thankYou(string $token): View
    {
        $form = $this->publicBookingFormService->findByToken($token);
        if ($form === null) {
            return view('public-booking.expired', [
                'message' => 'Tautan booking tidak ditemukan.',
            ]);
        }

        return view('public-booking.thank-you', [
            'token' => $token,
            'submittedPhone' => (string) session('submitted_phone', ''),
        ]);
    }
}
