<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ubah Booking</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-4 sm:p-6">
                <form method="POST" action="{{ route('admin.bookings.update', $booking) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Pelanggan</label>
                            <select name="customer_id" class="mt-1 w-full rounded-md border-gray-300">
                                <option value="">Pilih pelanggan</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id', $booking->customer_id) == $customer->id)>
                                        {{ $customer->name }} ({{ $customer->phone }})
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <label class="block text-sm font-medium text-gray-700">Layanan dan Jumlah Orang</label>
                            <button type="button" id="add-service-row" class="px-3 py-1.5 text-sm rounded-lg bg-rose-100 text-rose-700 hover:bg-rose-200">
                                + Layanan
                            </button>
                        </div>

                        @error('service_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        @error('services') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        @error('services.*.service_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        @error('services.*.people_count') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                        <div id="service-rows" class="mt-3 space-y-3"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                            <input type="date" name="booking_date" value="{{ old('booking_date', $booking->booking_date?->format('Y-m-d')) }}" class="mt-1 w-full rounded-md border-gray-300">
                            @error('booking_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Waktu</label>
                            <input type="time" id="booking_time" name="booking_time" value="{{ old('booking_time', substr((string) $booking->booking_time, 0, 5)) }}" class="mt-1 w-full rounded-md border-gray-300">
                            @error('booking_time') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Perkiraan Waktu Selesai</label>
                        <input type="text" id="estimated_end_time" readonly class="mt-1 w-full rounded-md border-gray-200 bg-gray-50 text-gray-700" value="{{ substr((string) $booking->end_time, 0, 5) }}">
                        <p class="mt-2 text-sm text-gray-600">Total orang: <span id="total_people_preview">{{ (int) ($booking->total_people ?? 0) }}</span></p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Lokasi</label>
                            <input type="text" name="location" value="{{ old('location', $booking->location) }}" placeholder="Alamat atau short Google Maps link" class="mt-1 w-full rounded-md border-gray-300">
                            @error('location') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Biaya Transportasi (Opsional)</label>
                            <input type="number" name="transport_fee" min="0" step="1000" value="{{ old('transport_fee', (int) round((float) ($booking->transport_fee ?? 0))) }}" class="mt-1 w-full rounded-md border-gray-300">
                            <p class="mt-1 text-xs text-gray-500">Isi `0` jika layanan di studio.</p>
                            @error('transport_fee') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" class="mt-1 w-full rounded-md border-gray-300">
                                @foreach (['pending', 'confirmed', 'completed', 'canceled'] as $status)
                                    <option value="{{ $status }}" @selected(old('status', $booking->status) === $status)>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Catatan</label>
                        <textarea name="notes" rows="4" class="mt-1 w-full rounded-md border-gray-300">{{ old('notes', $booking->notes) }}</textarea>
                        @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                        <a href="{{ route('admin.bookings.index') }}" class="w-full sm:w-auto text-center px-4 py-2 border border-gray-300 rounded-md text-gray-700">
                            Batal
                        </a>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-rose-600 text-white rounded-md hover:bg-rose-700">
                            Perbarui
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @php
        $servicesCatalog = $services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'price' => (float) $service->price,
                'duration' => (int) $service->duration,
            ];
        })->values();

        $existingServiceRows = $booking->bookingItems->map(function ($item) {
            return [
                'service_id' => (int) $item->service_id,
                'people_count' => max(1, (int) $item->people_count),
            ];
        })->values();

        if ($existingServiceRows->isEmpty()) {
            $existingServiceRows = collect([[
                'service_id' => (int) $booking->service_id,
                'people_count' => max(1, (int) ($booking->total_people ?? 1)),
            ]]);
        }
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const serviceRows = document.getElementById('service-rows');
            const addServiceRowButton = document.getElementById('add-service-row');
            const timeInput = document.getElementById('booking_time');
            const endInput = document.getElementById('estimated_end_time');
            const totalPeoplePreview = document.getElementById('total_people_preview');

            const servicesCatalog = @json($servicesCatalog);
            const existingRows = @json($existingServiceRows);
            const oldRows = @json(old('services', []));
            const fallbackServiceId = @json(old('service_id'));

            let rowIndex = 0;

            const renderServiceOptions = (selectedId = '') => {
                const options = ['<option value="">Pilih layanan</option>'];

                servicesCatalog.forEach((service) => {
                    const selected = String(selectedId) === String(service.id) ? 'selected' : '';

                    options.push(
                        `<option value="${service.id}" data-duration="${service.duration}" ${selected}>${service.name} - Rp ${new Intl.NumberFormat('id-ID').format(service.price)}</option>`
                    );
                });

                return options.join('');
            };

            const updateEstimate = () => {
                const timeValue = timeInput?.value;
                let totalDuration = 0;
                let totalPeople = 0;

                document.querySelectorAll('#service-rows [data-row-index]').forEach((row) => {
                    const select = row.querySelector('.service-select');
                    const peopleInput = row.querySelector('.people-count');
                    const selected = select?.selectedOptions?.[0];

                    if (!select?.value) {
                        return;
                    }

                    const duration = parseInt(selected?.dataset?.duration || '0', 10);
                    const people = Math.max(1, parseInt(peopleInput?.value || '1', 10));

                    totalDuration += Math.max(0, duration) * people;
                    totalPeople += people;
                });

                totalPeoplePreview.textContent = String(totalPeople);

                if (!timeValue || totalDuration <= 0) {
                    endInput.value = '-';
                    return;
                }

                const [hours, minutes] = timeValue.split(':').map(Number);
                const date = new Date(2000, 0, 1, hours, minutes + totalDuration);

                endInput.value = `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
            };

            const addServiceRow = (serviceId = '', peopleCount = 1) => {
                const row = document.createElement('div');

                row.className = 'grid grid-cols-1 sm:grid-cols-12 gap-3 p-3 rounded-xl border border-rose-100 bg-rose-50/40';
                row.dataset.rowIndex = String(rowIndex);

                row.innerHTML = `
                    <div class="sm:col-span-7 min-w-0">
                        <label class="block text-xs font-medium text-gray-600">Layanan</label>
                        <select name="services[${rowIndex}][service_id]" class="service-select mt-1 w-full rounded-md border-gray-300">
                            ${renderServiceOptions(serviceId)}
                        </select>
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-xs font-medium text-gray-600">Jumlah Orang</label>
                        <input type="number" min="1" max="50" name="services[${rowIndex}][people_count]" value="${Math.max(1, Number(peopleCount) || 1)}" class="people-count mt-1 w-full rounded-md border-gray-300">
                    </div>

                    <div class="sm:col-span-2 flex items-end">
                        <button type="button" class="remove-service-row w-full px-3 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100">
                            Hapus
                        </button>
                    </div>
                `;

                serviceRows.appendChild(row);
                rowIndex += 1;
                updateEstimate();
            };

            addServiceRowButton?.addEventListener('click', () => {
                addServiceRow('', 1);
            });

            serviceRows?.addEventListener('change', (event) => {
                if (event.target.classList.contains('service-select')) {
                    updateEstimate();
                }
            });

            serviceRows?.addEventListener('input', (event) => {
                if (event.target.classList.contains('people-count')) {
                    updateEstimate();
                }
            });

            serviceRows?.addEventListener('click', (event) => {
                if (!event.target.classList.contains('remove-service-row')) {
                    return;
                }

                const rows = serviceRows.querySelectorAll('[data-row-index]');

                if (rows.length <= 1) {
                    return;
                }

                event.target.closest('[data-row-index]')?.remove();
                updateEstimate();
            });

            timeInput?.addEventListener('input', updateEstimate);

            document.querySelector('form[action*="bookings"]')?.addEventListener('submit', () => {
                document.querySelectorAll('#service-rows [data-row-index]').forEach((row) => {
                    const select = row.querySelector('.service-select');

                    if (!select?.value) {
                        row.remove();
                    }
                });
            });

            if (Array.isArray(oldRows) && oldRows.length > 0) {
                oldRows.forEach((row) => addServiceRow(row.service_id || '', row.people_count || 1));
            } else if (Array.isArray(existingRows) && existingRows.length > 0) {
                existingRows.forEach((row) => addServiceRow(row.service_id || '', row.people_count || 1));
            } else if (fallbackServiceId) {
                addServiceRow(fallbackServiceId, 1);
            } else {
                addServiceRow('', 1);
            }

            updateEstimate();
        });
    </script>
</x-app-layout>
