<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePublicBookingFormRequest;
use App\Models\PublicBookingForm;
use App\Models\Service;
use App\Services\PublicBookingFormService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PublicBookingFormController extends Controller
{
    public function __construct(
        private readonly PublicBookingFormService $publicBookingFormService
    ) {
    }

    public function index(): View
    {
        $tenantId = (int) auth()->id();
        $tenant = auth()->user();

        return view('admin.booking-links.index', [
            'forms' => $this->publicBookingFormService->listForTenant($tenantId),
            'services' => Service::query()->orderBy('name')->get(['id', 'name']),
            'tenant' => $tenant,
        ]);
    }

    public function store(StorePublicBookingFormRequest $request): RedirectResponse
    {
        $tenantId = (int) auth()->id();
        $form = $this->publicBookingFormService->createForTenant(
            $tenantId,
            $request->validated('service_ids'),
            $request->validated('max_submissions'),
            $request->validated('transport_fee'),
            $request->validated('terms_title'),
            $request->validated('terms_content')
        );

        return redirect()
            ->route('admin.booking-links.index')
            ->with('success', 'Tautan booking berhasil dibuat: '.route('public.booking.show', $form->token));
    }

    public function deactivate(PublicBookingForm $publicBookingForm): RedirectResponse
    {
        $this->publicBookingFormService->deactivate($publicBookingForm);

        return redirect()
            ->route('admin.booking-links.index')
            ->with('success', 'Tautan booking berhasil dinonaktifkan.');
    }

    public function extend(PublicBookingForm $publicBookingForm): RedirectResponse
    {
        $updated = $this->publicBookingFormService->extendBy48Hours($publicBookingForm);

        return redirect()
            ->route('admin.booking-links.index')
            ->with('success', 'Tautan booking diperpanjang hingga '.$updated->expires_at->format('d M Y H:i').'.');
    }
}
