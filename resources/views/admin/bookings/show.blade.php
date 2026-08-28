<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-2xl text-stone-800 leading-tight">Detail Booking</h2>
            <a href="{{ route('admin.bookings.index') }}" class="px-5 py-2.5 rounded-xl border border-stone-300 text-stone-700 hover:bg-stone-50">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-rose-50 via-amber-50 to-white min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
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
            @endphp
            @if (session('success'))
                <div class="p-4 rounded-xl bg-emerald-100 text-emerald-800 border border-emerald-200">{{ session('success') }}</div>
            @endif
            @if ($errors->has('booking'))
                <div class="p-4 rounded-xl bg-red-100 text-red-700 border border-red-200">{{ $errors->first('booking') }}</div>
            @endif

            <div class="bg-white rounded-2xl border border-rose-100 shadow p-6">
                <h3 class="text-lg font-semibold text-stone-900">Informasi Booking</h3>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="p-4 rounded-xl bg-stone-50 border border-stone-200">
                        <p class="text-stone-500">Pelanggan</p>
                        <p class="mt-1 font-semibold text-stone-800">{{ $booking->customer->name }}</p>
                        <p class="text-stone-600">{{ $booking->customer->phone }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-stone-50 border border-stone-200">
                        <p class="text-stone-500">Layanan</p>
                        @if($booking->bookingItems->isNotEmpty())
                            <div class="mt-1 space-y-1">
                                @foreach($booking->bookingItems as $item)
                                    <p class="font-semibold text-stone-800">
                                        {{ $item->service->name }} x {{ $item->people_count }} orang
                                        <span class="font-normal text-stone-600">
                                            (Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }})
                                        </span>
                                    </p>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-1 font-semibold text-stone-800">{{ $booking->service->name }}</p>
                            <p class="text-stone-600">Rp {{ number_format((float) $booking->service->price, 0, ',', '.') }}</p>
                        @endif
                        <p class="mt-2 text-stone-600">
                            Biaya transport: Rp {{ number_format((float) ($booking->transport_fee ?? 0), 0, ',', '.') }}
                        </p>
                        <p class="mt-2 text-stone-600">Total orang: {{ $booking->total_people ?? 1 }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-stone-50 border border-stone-200">
                        <p class="text-stone-500">Jadwal</p>
                        <p class="mt-1 font-semibold text-stone-800">{{ $booking->booking_date?->format('d M Y') }}</p>
                        <p class="text-stone-600">{{ substr((string) $booking->booking_time, 0, 5) }} - {{ substr((string) $booking->end_time, 0, 5) }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-stone-50 border border-stone-200">
                        <p class="text-stone-500">Status Booking</p>
                        <p class="mt-2">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </p>
                    </div>
                    <div class="p-4 rounded-xl bg-stone-50 border border-stone-200">
                        <p class="text-stone-500">Pembayaran</p>
                        @php
                            $payment = $booking->payment;
                            $serviceSubtotal = (float) $booking->bookingItems->sum('subtotal');
                            if ($serviceSubtotal <= 0) {
                                $serviceSubtotal = (float) ($booking->service->price ?? 0);
                            }
                            $transportFee = max(0, (float) ($booking->transport_fee ?? 0));
                            $total = (float) ($payment?->amount ?? 0);
                            $dp = (float) ($payment?->dp_amount ?? 0);
                            $paid = (float) ($payment?->paid_amount ?? 0);
                            $remaining = max(0, $total - $paid);
                            $paymentLabel = $payment?->isSettled()
                                ? 'Lunas'
                                : (($payment?->isDpPaid() ?? false) ? 'DP Lunas, Menunggu Pelunasan' : 'Menunggu DP');
                        @endphp
                        <p class="mt-1 font-semibold text-stone-800">{{ $paymentLabel }}</p>
                        <p class="text-stone-600">Biaya layanan: Rp {{ number_format($serviceSubtotal, 0, ',', '.') }}</p>
                        <p class="text-stone-600">Biaya transport: Rp {{ number_format($transportFee, 0, ',', '.') }}</p>
                        <p class="text-stone-600">Total: Rp {{ number_format($total, 0, ',', '.') }}</p>
                        <p class="text-stone-600">DP Min.: Rp {{ number_format($dp, 0, ',', '.') }}</p>
                        <p class="text-stone-600">Terbayar: Rp {{ number_format($paid, 0, ',', '.') }}</p>
                        <p class="text-stone-600">Sisa: Rp {{ number_format($remaining, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-stone-50 border border-stone-200 md:col-span-2">
                        <p class="text-stone-500">Lokasi</p>
                        @php
                            $location = (string) ($booking->location ?? '');
                            $isMapLink = preg_match('/^https?:\/\//i', $location) === 1;
                        @endphp
                        @if($location === '')
                            <p class="mt-1 font-semibold text-stone-800">-</p>
                        @elseif($isMapLink)
                            <a href="{{ $location }}" target="_blank" rel="noopener noreferrer" class="mt-1 inline-flex items-center px-3 py-1.5 rounded-lg bg-rose-100 text-rose-700 hover:bg-rose-200">
                                Buka Google Maps
                            </a>
                            <p class="mt-2 text-xs text-stone-500 break-all">{{ $location }}</p>
                        @else
                            <p class="mt-1 font-semibold text-stone-800">{{ $location }}</p>
                        @endif
                    </div>
                </div>

                @php
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
                    $waMessage = rawurlencode(
                        "Halo {$booking->customer->name},\n".
                        "Berikut detail booking Anda:\n".
                        "- Layanan: {$booking->service->name}\n".
                        "- Jadwal: ".($booking->booking_date?->format('d M Y') ?? '-')." ".substr((string) $booking->booking_time, 0, 5)." - ".substr((string) $booking->end_time, 0, 5)."\n".
                        "- Jumlah orang: ".((int) ($booking->total_people ?? 1))."\n".
                        $waLocationSection."\n".
                        "Ringkasan pembayaran:\n".
                        "- Biaya Layanan: Rp ".number_format((float) $serviceSubtotal, 0, ',', '.')."\n".
                        "- Biaya Transport: Rp ".number_format((float) $transportFee, 0, ',', '.')."\n".
                        "- Total: Rp ".number_format((float) ($payment?->amount ?? 0), 0, ',', '.')."\n".
                        "- Terbayar: Rp ".number_format((float) ($payment?->paid_amount ?? 0), 0, ',', '.')."\n".
                        "- Sisa: Rp ".number_format(max(0, (float) (($payment?->amount ?? 0) - ($payment?->paid_amount ?? 0))), 0, ',', '.')."\n\n".
                        "Invoice akan kami kirimkan sebagai file PDF melalui chat ini. Terima kasih."
                    );
                @endphp
                <div class="mt-6 flex flex-wrap gap-3">
                    @if($waRawPhone !== '')
                        <a href="https://wa.me/{{ $waRawPhone }}?text={{ $waMessage }}" target="_blank" rel="noopener noreferrer" class="px-6 py-2.5 rounded-xl bg-green-600 text-white hover:bg-green-700">
                            WhatsApp
                        </a>
                    @endif
                    <a href="{{ route('admin.bookings.invoice.preview', $booking) }}" target="_blank" rel="noopener noreferrer" class="px-6 py-2.5 rounded-xl bg-stone-700 text-white hover:bg-stone-800">
                        Preview Invoice
                    </a>
                    <a href="{{ route('admin.bookings.invoice', $booking) }}" class="px-6 py-2.5 rounded-xl bg-amber-500 text-white hover:bg-amber-600">
                        Download Invoice
                    </a>
                    @if($booking->status !== \App\Models\Booking::STATUS_CANCELED)
                        <form method="POST" action="{{ route('admin.bookings.pay-now', $booking) }}">
                            @csrf
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-rose-500 text-white hover:bg-rose-600">
                                Pembayaran
                            </button>
                        </form>
                    @else
                        <span class="px-6 py-2.5 rounded-xl bg-stone-100 text-stone-500">
                            Pembayaran nonaktif
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


