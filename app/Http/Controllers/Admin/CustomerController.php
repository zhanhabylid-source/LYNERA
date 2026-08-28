<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Services\CustomerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerService $customerService
    ) {
    }

    public function index(): View
    {
        return view('admin.customers.index', [
            'customers' => $this->customerService->paginate(),
        ]);
    }

    public function create(): View
    {
        return view('admin.customers.create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $this->customerService->create($request->validated());

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Pelanggan berhasil dibuat.');
    }

    public function edit(int $customer): View
    {
        return view('admin.customers.edit', [
            'customer' => $this->customerService->findOrFail($customer),
        ]);
    }

    public function update(StoreCustomerRequest $request, int $customer): RedirectResponse
    {
        $model = $this->customerService->findOrFail($customer);
        $this->customerService->update($model, $request->validated());

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function destroy(int $customer): RedirectResponse
    {
        $model = $this->customerService->findOrFail($customer);

        try {
            $this->customerService->delete($model);
        } catch (\DomainException $e) {
            return redirect()
                ->route('admin.customers.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }
}
