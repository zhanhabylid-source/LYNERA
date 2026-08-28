<?php

namespace App\Services;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class CustomerService
{
    public function __construct(
        private readonly CustomerRepository $customerRepository
    ) {
    }

    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return $this->customerRepository->paginate($perPage);
    }

    public function allForSelect(): Collection
    {
        return $this->customerRepository->allForSelect();
    }

    public function findOrFail(int $id): Customer
    {
        return $this->customerRepository->findOrFail($id);
    }

    public function create(array $data): Customer
    {
        $data['phone'] = $this->normalizePhone($data['phone']);
        $data['tenant_id'] = (int) Auth::id();

        return $this->customerRepository->create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        if (isset($data['phone'])) {
            $data['phone'] = $this->normalizePhone($data['phone']);
        }

        return $this->customerRepository->update($customer, $data);
    }

    public function delete(Customer $customer): void
    {
        if ($customer->bookings()->exists()) {
            throw new \DomainException('Pelanggan ini masih memiliki booking dan tidak bisa dihapus.');
        }

        $this->customerRepository->delete($customer);
    }

    public function getTotalCustomers(): int
    {
        return $this->customerRepository->countAll();
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\s+/', '', $phone) ?? $phone;
    }
}
