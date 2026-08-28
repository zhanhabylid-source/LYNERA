<?php

namespace App\Repositories;

use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ServiceRepository
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Service::query()
            ->withCount(['bookings', 'bookingItems'])
            ->latest()
            ->paginate($perPage);
    }

    public function allForSelect(): Collection
    {
        return Service::query()
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'duration']);
    }

    public function findOrFail(int $id): Service
    {
        return Service::query()->findOrFail($id);
    }

    public function create(array $data): Service
    {
        return Service::query()->create($data);
    }

    public function update(Service $service, array $data): Service
    {
        $service->update($data);

        return $service->refresh();
    }

    public function delete(Service $service): void
    {
        $service->delete();
    }
}
