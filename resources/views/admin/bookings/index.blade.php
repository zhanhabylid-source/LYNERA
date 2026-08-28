<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">Booking</h2>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <a href="{{ route('admin.booking-links.index') }}" class="px-4 py-2.5 bg-stone-700 text-white rounded-xl hover:bg-stone-800 transition min-h-[44px] flex items-center justify-center">
                    Link Booking
                </a>
                <a href="{{ route('admin.bookings.create') }}" class="px-4 py-2.5 bg-rose-500 text-white rounded-xl hover:bg-rose-600 transition min-h-[44px] flex items-center justify-center">
                    Tambah Booking
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-rose-50 via-amber-50 to-white min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 rounded-xl bg-emerald-100 text-emerald-700 border border-emerald-200">{{ session('success') }}</div>
            @endif
            @if ($errors->has('booking'))
                <div class="mb-4 p-4 rounded-xl bg-red-100 text-red-700 border border-red-200">{{ $errors->first('booking') }}</div>
            @endif

            @php
                $termsTitle = old('booking_terms_title', trim((string) ($tenant?->booking_terms_title ?? '')));
                $termsContent = old('booking_terms_content', trim((string) ($tenant?->booking_terms_content ?? '')));
            @endphp
            <div class="mb-6 bg-white shadow rounded-2xl border border-rose-100 p-6">
                <h3 class="text-lg font-semibold text-stone-900">T&C Booking</h3>
                <p class="mt-1 text-sm text-stone-600">Informasi ini tampil sebagai acuan T&C booking tenant dan bisa dipakai sebagai default saat membuat link booking.</p>

                @if($termsTitle !== '' || $termsContent !== '')
                    <div class="mt-4 rounded-xl border border-stone-200 bg-stone-50 p-4">
                        <p class="text-sm font-semibold text-stone-800">
                            {{ $termsTitle !== '' ? $termsTitle : 'Syarat & Ketentuan Booking' }}
                        </p>
                        <p class="mt-2 whitespace-pre-line text-sm text-stone-700">
                            {{ $termsContent !== '' ? $termsContent : '-' }}
                        </p>
                        @if($tenant?->booking_terms_updated_at)
                            <p class="mt-2 text-xs text-stone-500">Terakhir diperbarui: {{ $tenant->booking_terms_updated_at->format('d M Y H:i') }}</p>
                        @endif
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.bookings.terms.update') }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-sm font-medium text-stone-700">Judul T&C</label>
                        <input
                            type="text"
                            name="booking_terms_title"
                            value="{{ $termsTitle !== '' ? $termsTitle : 'Syarat & Ketentuan Booking' }}"
                            required
                            class="mt-1 w-full rounded-xl border-stone-300 focus:border-rose-400 focus:ring-rose-300"
                        >
                        @error('booking_terms_title') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700">Isi T&C</label>
                        <textarea
                            name="booking_terms_content"
                            rows="6"
                            required
                            class="mt-1 w-full rounded-xl border-stone-300 focus:border-rose-400 focus:ring-rose-300"
                            placeholder="Contoh: DP tidak dapat dikembalikan jika booking dibatalkan customer."
                        >{{ $termsContent !== '' ? $termsContent : "1. Booking dianggap valid setelah DP diterima.\n2. Jadwal dapat diubah maksimal H-2 sebelum hari layanan.\n3. DP yang sudah dibayar tidak dapat direfund jika booking dibatalkan customer." }}</textarea>
                        @error('booking_terms_content') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-500 text-white hover:bg-rose-600 transition">
                        Simpan T&C
                    </button>
                </form>
            </div>

            <div class="bg-white shadow rounded-2xl border border-rose-100 overflow-hidden">
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelanggan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Layanan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ringkasan Biaya</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe Layanan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Selesai</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($bookings as $booking)
                                @php
                                    $statusLabel = match ($booking->status) {
                                        'pending' => 'Menunggu',
                                        'confirmed' => 'Dikonfirmasi',
                                        'completed' => 'Selesai',
                                        'canceled' => 'Batal',
                                        default => ucfirst((string) $booking->status),
                                    };
                                    $statusClass = match ($booking->status) {
                                        'pending' => 'bg-amber-100 text-amber-700',
                                        'confirmed' => 'bg-blue-100 text-blue-700',
                                        'completed' => 'bg-emerald-100 text-emerald-700',
                                        'canceled' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    };
                                    $waRawPhone = preg_replace('/\D+/', '', (string) ($booking->customer->phone ?? '')) ?? '';
                                    if (str_starts_with($waRawPhone, '0')) {
                                        $waRawPhone = '62'.substr($waRawPhone, 1);
                                    } elseif ($waRawPhone !== '' && !str_starts_with($waRawPhone, '62')) {
                                        $waRawPhone = '62'.$waRawPhone;
                                    }
                                    $tenantName = trim((string) ($booking->tenant?->name ?? 'MUA Kami'));
                                    $bookingLocation = trim((string) ($booking->location ?? ''));
                                    $studioMaps = trim((string) ($booking->tenant?->studio_maps_link ?? ''));
                                    $studioAddress = trim((string) ($booking->tenant?->studio_location ?? ''));
                                    $isStudioService = $bookingLocation !== '' && ($bookingLocation === $studioMaps || $bookingLocation === $studioAddress);
                                    if ($isStudioService) {
                                        $studioLocationForCustomer = $studioMaps !== '' ? $studioMaps : ($studioAddress !== '' ? $studioAddress : $bookingLocation);
                                        $waLocationSection =
                                            "- Jenis Layanan: Studio {$tenantName}\n".
                                            "- Lokasi Studio: {$studioLocationForCustomer}\n".
                                            "- Konfirmasi: Lokasi Anda menggunakan layanan {$tenantName} di lokasi berikut (tautan map di atas).\n";
                                    } else {
                                        $homeLocationForCustomer = $bookingLocation !== '' ? $bookingLocation : '-';
                                        $waLocationSection =
                                            "- Jenis Layanan: Home Service\n".
                                            "- Lokasi Home Service: {$homeLocationForCustomer}\n".
                                            "- Konfirmasi: Alamat/lokasi di atas adalah lokasi yang Anda tambahkan pada form booking.\n";
                                    }
                                    $serviceSubtotal = (float) $booking->bookingItems->sum('subtotal');
                                    if ($serviceSubtotal <= 0) {
                                        $serviceSubtotal = (float) ($booking->service->price ?? 0);
                                    }
                                    $transportFee = max(0, (float) ($booking->transport_fee ?? 0));
                                    $estimatedTotal = $serviceSubtotal + $transportFee;
                                    $waMessage = rawurlencode(
                                        "Halo {$booking->customer->name},\n".
                                        "Ini ringkasan booking Anda:\n".
                                        "- Layanan: {$booking->service->name}\n".
                                        "- Jadwal: ".($booking->booking_date?->format('d M Y') ?? '-')." ".substr((string) $booking->booking_time, 0, 5)." - ".substr((string) $booking->end_time, 0, 5)."\n".
                                        $waLocationSection.
                                        "- Biaya Layanan: Rp ".number_format($serviceSubtotal, 0, ',', '.')."\n".
                                        "- Biaya Transport: Rp ".number_format($transportFee, 0, ',', '.')."\n".
                                        "- Estimasi Total: Rp ".number_format($estimatedTotal, 0, ',', '.')."\n".
                                        "- Status: {$statusLabel}\n\n".
                                        "Invoice dan detail lengkap akan kami kirimkan melalui chat ini. Silakan hubungi kami jika ada penyesuaian jadwal."
                                    );
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $booking->customer->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $booking->service->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        <p>Layanan: Rp {{ number_format($serviceSubtotal, 0, ',', '.') }}</p>
                                        <p>Transport: Rp {{ number_format($transportFee, 0, ',', '.') }}</p>
                                        <p class="font-semibold text-stone-800">Total: Rp {{ number_format($estimatedTotal, 0, ',', '.') }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @php
                                            $location = trim((string) ($booking->location ?? ''));
                                            $studioMaps = trim((string) ($booking->tenant?->studio_maps_link ?? ''));
                                            $studioAddress = trim((string) ($booking->tenant?->studio_location ?? ''));
                                            $isStudio = $location !== '' && ($location === $studioMaps || $location === $studioAddress);
                                            $serviceType = $isStudio ? 'Di Studio Kami' : 'Layanan ke Rumah';
                                            $serviceTypeClass = $isStudio
                                                ? 'bg-blue-100 text-blue-700'
                                                : 'bg-rose-100 text-rose-700';
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $serviceTypeClass }}">
                                            {{ $serviceType }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $booking->booking_date?->format('d M Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ substr((string) $booking->booking_time, 0, 5) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ substr((string) $booking->end_time, 0, 5) }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a href="{{ route('admin.bookings.show', $booking) }}" class="px-3 py-1.5 text-sm bg-stone-600 text-white rounded-lg hover:bg-stone-700">Detail</a>
                                            @if($waRawPhone !== '')
                                                <a href="https://wa.me/{{ $waRawPhone }}?text={{ $waMessage }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700">WhatsApp</a>
                                            @endif
                                            <a href="{{ route('admin.bookings.invoice.preview', $booking) }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 text-sm bg-stone-700 text-white rounded-lg hover:bg-stone-800">Preview</a>
                                            <a href="{{ route('admin.bookings.invoice', $booking) }}" class="px-3 py-1.5 text-sm bg-amber-500 text-white rounded-lg hover:bg-amber-600">Invoice</a>
                                            @if($booking->status !== \App\Models\Booking::STATUS_CANCELED)
                                                <form method="POST" action="{{ route('admin.bookings.pay-now', $booking) }}">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1.5 text-sm bg-rose-500 text-white rounded-lg hover:bg-rose-600">Pembayaran</button>
                                                </form>
                                            @else
                                                <span class="px-3 py-1.5 text-sm rounded-lg bg-stone-100 text-stone-500">Pembayaran nonaktif</span>
                                            @endif
                                            @if($booking->hasServicePassed())
                                                <span class="px-3 py-1.5 text-sm rounded-lg bg-stone-100 text-stone-500">
                                                    Booking berlalu (read only)
                                                </span>
                                            @else
                                                <a href="{{ route('admin.bookings.edit', $booking) }}" class="px-3 py-1.5 text-sm bg-blue-500 text-white rounded-lg hover:bg-blue-600">Ubah</a>
                                                <form method="POST" action="{{ route('admin.bookings.destroy', $booking) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700" onclick="return confirm('Hapus booking ini?')">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-12 text-center">
                                        <div class="inline-flex flex-col items-center gap-4 rounded-2xl border border-dashed border-rose-200 bg-rose-50/80 px-8 py-8 max-w-sm">
                                            <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl border border-rose-100 bg-white">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                            </div>
                                            <div class="text-center space-y-2">
                                                <p class="text-base font-semibold text-stone-900">Belum ada booking</p>
                                                <p class="text-sm leading-6 text-stone-600">Mulai terima booking dari klien Anda. Buat booking baru atau bagikan link booking untuk memudahkan klien booking Anda.</p>
                                            </div>
                                            <div class="flex flex-col gap-2 w-full pt-2">
                                                <a href="{{ route('admin.bookings.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-600">
                                                    Buat Booking Pertama
                                                </a>
                                                <a href="{{ route('admin.booking-links.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">
                                                    Lihat Link Booking
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="md:hidden p-4 space-y-3">
                    @forelse ($bookings as $booking)
                        @php
                            $statusLabel = match ($booking->status) {
                                'pending' => 'Menunggu',
                                'confirmed' => 'Dikonfirmasi',
                                'completed' => 'Selesai',
                                'canceled' => 'Batal',
                                default => ucfirst((string) $booking->status),
                            };
                            $statusClass = match ($booking->status) {
                                'pending' => 'bg-amber-100 text-amber-700',
                                'confirmed' => 'bg-blue-100 text-blue-700',
                                'completed' => 'bg-emerald-100 text-emerald-700',
                                'canceled' => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                            $waRawPhone = preg_replace('/\D+/', '', (string) ($booking->customer->phone ?? '')) ?? '';
                            if (str_starts_with($waRawPhone, '0')) {
                                $waRawPhone = '62'.substr($waRawPhone, 1);
                            } elseif ($waRawPhone !== '' && !str_starts_with($waRawPhone, '62')) {
                                $waRawPhone = '62'.$waRawPhone;
                            }
                            $tenantName = trim((string) ($booking->tenant?->name ?? 'MUA Kami'));
                            $bookingLocation = trim((string) ($booking->location ?? ''));
                            $studioMaps = trim((string) ($booking->tenant?->studio_maps_link ?? ''));
                            $studioAddress = trim((string) ($booking->tenant?->studio_location ?? ''));
                            $isStudioService = $bookingLocation !== '' && ($bookingLocation === $studioMaps || $bookingLocation === $studioAddress);
                            if ($isStudioService) {
                                $studioLocationForCustomer = $studioMaps !== '' ? $studioMaps : ($studioAddress !== '' ? $studioAddress : $bookingLocation);
                                $waLocationSection =
                                    "- Jenis Layanan: Studio {$tenantName}\n".
                                    "- Lokasi Studio: {$studioLocationForCustomer}\n".
                                    "- Konfirmasi: Lokasi Anda menggunakan layanan {$tenantName} di lokasi berikut (tautan map di atas).\n";
                            } else {
                                $homeLocationForCustomer = $bookingLocation !== '' ? $bookingLocation : '-';
                                $waLocationSection =
                                    "- Jenis Layanan: Home Service\n".
                                    "- Lokasi Home Service: {$homeLocationForCustomer}\n".
                                    "- Konfirmasi: Alamat/lokasi di atas adalah lokasi yang Anda tambahkan pada form booking.\n";
                            }
                            $serviceSubtotal = (float) $booking->bookingItems->sum('subtotal');
                            if ($serviceSubtotal <= 0) {
                                $serviceSubtotal = (float) ($booking->service->price ?? 0);
                            }
                            $transportFee = max(0, (float) ($booking->transport_fee ?? 0));
                            $estimatedTotal = $serviceSubtotal + $transportFee;
                            $waMessage = rawurlencode(
                                "Halo {$booking->customer->name},\n".
                                "Ini ringkasan booking Anda:\n".
                                "- Layanan: {$booking->service->name}\n".
                                "- Jadwal: ".($booking->booking_date?->format('d M Y') ?? '-')." ".substr((string) $booking->booking_time, 0, 5)." - ".substr((string) $booking->end_time, 0, 5)."\n".
                                $waLocationSection.
                                "- Biaya Layanan: Rp ".number_format($serviceSubtotal, 0, ',', '.')."\n".
                                "- Biaya Transport: Rp ".number_format($transportFee, 0, ',', '.')."\n".
                                "- Estimasi Total: Rp ".number_format($estimatedTotal, 0, ',', '.')."\n".
                                "- Status: {$statusLabel}\n\n".
                                "Invoice dan detail lengkap akan kami kirimkan melalui chat ini. Silakan hubungi kami jika ada penyesuaian jadwal."
                            );
                        @endphp
                        <div class="rounded-xl border border-rose-100 bg-rose-50/40 p-4 shadow-sm">
                            <p class="text-sm font-semibold text-stone-900">{{ $booking->customer->name }}</p>
                            <p class="text-sm text-stone-700 mt-1">{{ $booking->service->name }}</p>
                            @php
                                $location = trim((string) ($booking->location ?? ''));
                                $studioMaps = trim((string) ($booking->tenant?->studio_maps_link ?? ''));
                                $studioAddress = trim((string) ($booking->tenant?->studio_location ?? ''));
                                $isStudio = $location !== '' && ($location === $studioMaps || $location === $studioAddress);
                                $serviceType = $isStudio ? 'Di Studio Kami' : 'Layanan ke Rumah';
                                $serviceTypeClass = $isStudio
                                    ? 'bg-blue-100 text-blue-700'
                                    : 'bg-rose-100 text-rose-700';
                            @endphp
                            <p class="mt-2">
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $serviceTypeClass }}">
                                    {{ $serviceType }}
                                </span>
                            </p>
                            <p class="text-sm text-stone-600 mt-1">
                                {{ $booking->booking_date?->format('d M Y') }} | {{ substr((string) $booking->booking_time, 0, 5) }} - {{ substr((string) $booking->end_time, 0, 5) }}
                            </p>
                            <p class="text-sm text-stone-600 mt-1">
                                Biaya layanan Rp {{ number_format($serviceSubtotal, 0, ',', '.') }} | Transport Rp {{ number_format($transportFee, 0, ',', '.') }}
                            </p>
                            <p class="text-sm font-semibold text-stone-800">
                                Estimasi total Rp {{ number_format($estimatedTotal, 0, ',', '.') }}
                            </p>
                            <p class="text-sm mt-2">
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <a href="{{ route('admin.bookings.show', $booking) }}" class="px-3 py-1.5 text-sm bg-stone-600 text-white rounded-lg hover:bg-stone-700">Detail</a>
                                @if($waRawPhone !== '')
                                    <a href="https://wa.me/{{ $waRawPhone }}?text={{ $waMessage }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700">WhatsApp</a>
                                @endif
                                <a href="{{ route('admin.bookings.invoice.preview', $booking) }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 text-sm bg-stone-700 text-white rounded-lg hover:bg-stone-800">Preview</a>
                                <a href="{{ route('admin.bookings.invoice', $booking) }}" class="px-3 py-1.5 text-sm bg-amber-500 text-white rounded-lg hover:bg-amber-600">Invoice</a>
                                @if($booking->status !== \App\Models\Booking::STATUS_CANCELED)
                                    <form method="POST" action="{{ route('admin.bookings.pay-now', $booking) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 text-sm bg-rose-500 text-white rounded-lg hover:bg-rose-600">Pembayaran</button>
                                    </form>
                                @else
                                    <span class="px-3 py-1.5 text-sm rounded-lg bg-stone-100 text-stone-500">Pembayaran nonaktif</span>
                                @endif
                                @if($booking->hasServicePassed())
                                    <span class="px-3 py-1.5 text-sm rounded-lg bg-stone-100 text-stone-500">
                                        Booking berlalu (read only)
                                    </span>
                                @else
                                    <a href="{{ route('admin.bookings.edit', $booking) }}" class="px-3 py-1.5 text-sm bg-blue-500 text-white rounded-lg hover:bg-blue-600">Ubah</a>
                                    <form method="POST" action="{{ route('admin.bookings.destroy', $booking) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700" onclick="return confirm('Hapus booking ini?')">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-stone-300 bg-stone-50 p-4 text-sm text-stone-500">
                            Belum ada booking.
                        </div>
                    @endforelse
                </div>
                <div class="p-4">
                    {{ $bookings->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

