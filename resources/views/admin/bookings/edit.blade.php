<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ubah Booking</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <form method="POST" action="{{ route('admin.bookings.update', $booking) }}" class="space-y-4">
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
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Layanan</label>
                            <select name="service_id" id="service_id" class="mt-1 w-full rounded-md border-gray-300">
                                <option value="">Pilih layanan</option>
                                @foreach ($services as $service)
                                    <option value="{{ $service->id }}" data-duration="{{ $service->duration }}" @selected(old('service_id', $booking->service_id) == $service->id)>
                                        {{ $service->name }} - Rp {{ number_format($service->price, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
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

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('admin.bookings.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700">Batal</a>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white rounded-md hover:bg-rose-700">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const serviceInput = document.getElementById('service_id');
            const timeInput = document.getElementById('booking_time');
            const endInput = document.getElementById('estimated_end_time');

            const updateEstimate = () => {
                const selected = serviceInput?.selectedOptions?.[0];
                const duration = parseInt(selected?.dataset?.duration || '0', 10);
                const timeValue = timeInput?.value;

                if (!timeValue || Number.isNaN(duration) || duration <= 0) {
                    endInput.value = '-';
                    return;
                }

                const [hours, minutes] = timeValue.split(':').map(Number);
                const date = new Date(2000, 0, 1, hours, minutes + duration);
                endInput.value = `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
            };

            serviceInput?.addEventListener('change', updateEstimate);
            timeInput?.addEventListener('input', updateEstimate);
            updateEstimate();
        });
    </script>
</x-app-layout>


