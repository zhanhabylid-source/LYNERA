<?php

namespace App\Services;

use App\Models\Service;
use App\Repositories\ServiceRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class ServiceService
{
    public function __construct(
        private readonly ServiceRepository $serviceRepository
    ) {
    }

    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return $this->serviceRepository->paginate($perPage);
    }

    public function allForSelect(): Collection
    {
        return $this->serviceRepository->allForSelect();
    }

    public function findOrFail(int $id): Service
    {
        return $this->serviceRepository->findOrFail($id);
    }

    public function create(array $data): Service
    {
        $data['tenant_id'] = (int) Auth::id();

        return $this->serviceRepository->create($data);
    }

    public function update(Service $service, array $data): Service
    {
        return $this->serviceRepository->update($service, $data);
    }

    public function delete(Service $service): void
    {
        $service->loadCount(['bookings', 'bookingItems']);
        $usageCount = (int) (($service->bookings_count ?? 0) + ($service->booking_items_count ?? 0));

        if ($usageCount > 0) {
            throw new InvalidArgumentException(
                'Layanan tidak dapat dihapus karena sudah dipakai pada booking. '
                .'Ubah layanan di booking terlebih dahulu atau buat layanan baru sebagai pengganti.'
            );
        }

        $this->serviceRepository->delete($service);
    }
}
