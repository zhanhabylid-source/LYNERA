<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CustomerRepository
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Customer::query()
            ->withCount('bookings')
            ->latest()
            ->paginate($perPage);
    }

    public function allForSelect(): Collection
    {
        return Customer::query()
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);
    }

    public function findOrFail(int $id): Customer
    {
        return Customer::query()->findOrFail($id);
    }

    public function create(array $data): Customer
    {
        return Customer::query()->create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer->refresh();
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }

    public function countAll(): int
    {
        return Customer::query()->count();
    }
}
