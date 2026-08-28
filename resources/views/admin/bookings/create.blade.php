<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Buat Booking</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <form method="POST" action="{{ route('admin.bookings.store') }}" class="space-y-4" data-testid="booking-create-form">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Pelanggan</label>
                            <select name="customer_id" class="mt-1 w-full rounded-md border-gray-300" data-testid="booking-customer-select">
                                <option value="">Pilih pelanggan</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                        {{ $customer->name }} ({{ $customer->phone }})
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">Layanan dan Jumlah Orang</label>
                            <button type="button" id="add-service-row" class="px-3 py-1.5 text-sm rounded-lg bg-rose-100 text-rose-700 hover:bg-rose-200" data-testid="booking-add-service-button">
                                + Layanan
                            </button>
                        </div>
                        @error('service_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        @error('services') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        <div id="service-rows" class="mt-3 space-y-3"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                            <input type="date" name="booking_date" value="{{ old('booking_date') }}" class="mt-1 w-full rounded-md border-gray-300" data-testid="booking-date-input">
                            @error('booking_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Waktu</label>
                            <input type="time" id="booking_time" name="booking_time" value="{{ old('booking_time') }}" class="mt-1 w-full rounded-md border-gray-300" data-testid="booking-time-input">
                            @error('booking_time') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Perkiraan Waktu Selesai</label>
                        <input type="text" id="estimated_end_time" readonly class="mt-1 w-full rounded-md border-gray-200 bg-gray-50 text-gray-700" value="-">
                        <p class="mt-2 text-sm text-gray-600">Total orang: <span id="total_people_preview">0</span></p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Lokasi</label>
                            <input type="text" name="location" value="{{ old('location') }}" placeholder="Alamat atau short Google Maps link" class="mt-1 w-full rounded-md border-gray-300">
                            @error('location') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Biaya Transportasi (Opsional)</label>
                            <input type="number" name="transport_fee" min="0" step="1000" value="{{ old('transport_fee', 0) }}" class="mt-1 w-full rounded-md border-gray-300">
                            <p class="mt-1 text-xs text-gray-500">Isi `0` jika layanan di studio.</p>
                            @error('transport_fee') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <input type="hidden" name="status" value="pending">
                            <input type="text" value="Pending (otomatis saat booking baru)" readonly class="mt-1 w-full rounded-md border-gray-200 bg-gray-50 text-gray-700">
                            @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Catatan</label>
                        <textarea name="notes" rows="4" class="mt-1 w-full rounded-md border-gray-300">{{ old('notes') }}</textarea>
                        @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('admin.bookings.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700">Batal</a>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white rounded-md hover:bg-rose-700" data-testid="booking-submit-button">Simpan</button>
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
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const serviceRows = document.getElementById('service-rows');
            const addLayananRowButton = document.getElementById('add-service-row');
            const timeInput = document.getElementById('booking_time');
            const endInput = document.getElementById('estimated_end_time');
            const totalPeoplePreview = document.getElementById('total_people_preview');

            const servicesCatalog = @json($servicesCatalog);

            const oldRows = @json(old('services', []));
            const fallbackLayananId = @json(old('service_id'));
            const renderLayananOptions = (selectedId = '') => {
                const options = ['<option value="">Pilih layanan</option>'];
                servicesCatalog.forEach((service) => {
                    const selected = String(selectedId) === String(service.id) ? 'selected' : '';
                    options.push(
                        `<option value="${service.id}" data-duration="${service.duration}" data-price="${service.price}" ${selected}>${service.name} - Rp ${new Intl.NumberFormat('id-ID').format(service.price)}</option>`
                    );
                });
                return options.join('');
            };

            let rowIndex = 0;

            const addLayananRow = (serviceId = '', peopleCount = 1) => {
                const row = document.createElement('div');
                row.className = 'grid grid-cols-1 md:grid-cols-12 gap-3 p-3 rounded-xl border border-rose-100 bg-rose-50/40';
                row.dataset.rowIndex = String(rowIndex);
                row.innerHTML = `
                    <div class="md:col-span-7">
                        <label class="block text-xs font-medium text-gray-600">Layanan</label>
                        <select name="services[${rowIndex}][service_id]" class="service-select mt-1 w-full rounded-md border-gray-300">
                            ${renderLayananOptions(serviceId)}
                        </select>
                        <p class="service-error mt-1 text-sm text-red-600"></p>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-medium text-gray-600">Jumlah Orang</label>
                        <input type="number" min="1" name="services[${rowIndex}][people_count]" value="${peopleCount}" class="people-count mt-1 w-full rounded-md border-gray-300" />
                        <p class="people-error mt-1 text-sm text-red-600"></p>
                    </div>
                    <div class="md:col-span-2 flex items-end">
                        <button type="button" class="remove-service-row w-full px-3 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100">Hapus</button>
                    </div>
                `;

                serviceRows.appendChild(row);
                rowIndex += 1;
                updateEstimate();
            };

            const updateEstimate = () => {
                const timeValue = timeInput?.value;
                let totalDuration = 0;
                let totalPeople = 0;

                document.querySelectorAll('#service-rows [data-row-index]').forEach((row) => {
                    const select = row.querySelector('.service-select');
                    const peopleInput = row.querySelector('.people-count');
                    const selected = select?.selectedOptions?.[0];
                    // Skip empty rows (no service picked) so the preview reflects reality.
                    if (!select?.value) {
                        return;
                    }
                    const baseDuration = parseInt(selected?.dataset?.duration || '0', 10);
                    const people = Math.max(1, parseInt(peopleInput?.value || '1', 10));
                    if (baseDuration > 0) {
                        totalDuration += baseDuration * people;
                    }
                    totalPeople += people;
                });

                totalPeoplePreview.textContent = String(totalPeople);

                if (!timeValue || Number.isNaN(totalDuration) || totalDuration <= 0) {
                    endInput.value = '-';
                    return;
                }

                const [hours, minutes] = timeValue.split(':').map(Number);
                const date = new Date(2000, 0, 1, hours, minutes + totalDuration);
                endInput.value = `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
            };

            addLayananRowButton?.addEventListener('click', () => addLayananRow('', 1));
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
                const target = event.target;
                if (target.classList.contains('remove-service-row')) {
                    target.closest('[data-row-index]')?.remove();
                    updateEstimate();
                }
            });
            timeInput?.addEventListener('input', updateEstimate);

            // On submit, prune empty rows so the backend never receives an unfilled row
            // (previously would fail validation with a confusing message).
            const bookingForm = document.querySelector('form[action*="bookings"]');
            bookingForm?.addEventListener('submit', () => {
                document.querySelectorAll('#service-rows [data-row-index]').forEach((row) => {
                    const select = row.querySelector('.service-select');
                    if (!select?.value) {
                        row.remove();
                    }
                });
            });

            if (Array.isArray(oldRows) && oldRows.length > 0) {
                oldRows.forEach((row) => addLayananRow(row.service_id || '', row.people_count || 1));
            } else if (fallbackLayananId) {
                addLayananRow(fallbackLayananId, 1);
            } else {
                addLayananRow('', 1);
            }

            updateEstimate();
        });
    </script>
</x-app-layout>


