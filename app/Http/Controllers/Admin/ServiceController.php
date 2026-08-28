<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Services\ServiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use InvalidArgumentException;

class ServiceController extends Controller
{
    public function __construct(
        private readonly ServiceService $serviceService
    ) {
    }

    public function index(): View
    {
        return view('admin.services.index', [
            'services' => $this->serviceService->paginate(),
        ]);
    }

    public function create(): View
    {
        return view('admin.services.create');
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $this->serviceService->create($request->validated());

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Layanan berhasil dibuat.');
    }

    public function edit(int $service): View
    {
        return view('admin.services.edit', [
            'service' => $this->serviceService->findOrFail($service),
        ]);
    }

    public function update(StoreServiceRequest $request, int $service): RedirectResponse
    {
        $model = $this->serviceService->findOrFail($service);
        $this->serviceService->update($model, $request->validated());

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy(int $service): RedirectResponse
    {
        $model = $this->serviceService->findOrFail($service);

        try {
            $this->serviceService->delete($model);
        } catch (InvalidArgumentException $exception) {
            return redirect()
                ->route('admin.services.index')
                ->withErrors(['service' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Layanan berhasil dihapus.');
    }
}
