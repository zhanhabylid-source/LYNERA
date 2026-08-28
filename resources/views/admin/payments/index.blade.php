<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-2xl text-stone-800 leading-tight">Pembayaran Booking</h2>
            @if(!empty($bookingId))
                <a href="{{ route('admin.payments.index') }}" class="px-4 py-2 rounded-xl border border-stone-300 text-sm text-stone-700 hover:bg-stone-50">
                    Lihat Semua Pembayaran
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-rose-50 via-amber-50 to-white min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-4 rounded-xl bg-emerald-100 text-emerald-800 border border-emerald-200">{{ session('success') }}</div>
            @endif
            @if ($errors->has('payment'))
                <div class="p-4 rounded-xl bg-red-100 text-red-700 border border-red-200">{{ $errors->first('payment') }}</div>
            @endif

            @if(!empty($bookingId))
                <div class="p-4 rounded-xl bg-rose-100 text-rose-800 border border-rose-200 text-sm">
                    Anda sedang melihat pembayaran untuk booking #{{ $bookingId }}.
                </div>
            @endif

            <div class="p-4 rounded-xl bg-stone-100 text-stone-700 border border-stone-200 text-sm">
                Tips: Isi nominal DP sesuai kesepakatan customer (fleksibel), atau klik <span class="font-semibold">Lunas</span> untuk mengisi penuh otomatis.
            </div>

            <div class="space-y-4">
                @forelse($payments as $payment)
                    @php
                        $total = (float) $payment->amount;
                        $rawSubtotal = (float) $payment->booking->bookingItems->sum('subtotal');
                        $serviceSubtotal = $rawSubtotal > 0 ? $rawSubtotal : (float) ($payment->booking->service->price ?? 0);
                        $transportFee = max(0, (float) ($payment->booking->transport_fee ?? 0));
                        $discount = max(0, (float) $payment->discount_amount);
                        $dp = (float) $payment->dp_amount;
                        $paid = (float) $payment->paid_amount;
                        $remaining = max(0, $total - $paid);
                        $isDpPaid = $payment->isDpPaid();
                        $isSettled = $payment->isSettled();
                        $canSettle = $isDpPaid
                            && ! $isSettled
                            && $payment->booking->status !== \App\Models\Booking::STATUS_CANCELED
                            && ! $payment->booking->hasServicePassed();
                        $canCancelBooking = ! in_array($payment->booking->status, [\App\Models\Booking::STATUS_CANCELED, \App\Models\Booking::STATUS_COMPLETED], true)
                            && ! $payment->booking->hasServicePassed();
                        $statusLabel = $isSettled
                            ? 'Lunas'
                            : ($isDpPaid ? 'DP Masuk, Menunggu Pelunasan' : 'Menunggu DP');
                        $statusClass = $isSettled
                            ? 'bg-emerald-100 text-emerald-700 border-emerald-200'
                            : ($isDpPaid ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-amber-100 text-amber-700 border-amber-200');
                    @endphp

                    <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div>
                                <p class="text-xs text-stone-500">Booking #{{ $payment->booking_id }}</p>
                                <h3 class="text-lg font-semibold text-stone-900">{{ $payment->booking->customer->name }}</h3>
                                <p class="text-sm text-stone-600 mt-1">{{ $payment->booking->service->name }}</p>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
                            <div class="rounded-xl border border-stone-200 bg-stone-50 p-3">
                                <p class="text-stone-500">Biaya Layanan</p>
                                <p class="font-semibold text-stone-900">Rp {{ number_format($serviceSubtotal, 0, ',', '.') }}</p>
                            </div>
                            <div class="rounded-xl border border-stone-200 bg-stone-50 p-3">
                                <p class="text-stone-500">Biaya Transport</p>
                                <p class="font-semibold text-stone-900">Rp {{ number_format($transportFee, 0, ',', '.') }}</p>
                            </div>
                            <div class="rounded-xl border border-stone-200 bg-stone-50 p-3">
                                <p class="text-stone-500">Diskon</p>
                                <p class="font-semibold text-rose-700">Rp {{ number_format($discount, 0, ',', '.') }}</p>
                            </div>
                            <div class="rounded-xl border border-rose-200 bg-rose-50 p-3">
                                <p class="text-stone-600">Total Tagihan</p>
                                <p class="font-bold text-stone-900">Rp {{ number_format($total, 0, ',', '.') }}</p>
                            </div>
                            <div class="rounded-xl border border-stone-200 bg-white p-3">
                                <p class="text-stone-500">DP Minimal</p>
                                <p class="font-semibold text-stone-900">Rp {{ number_format($dp, 0, ',', '.') }}</p>
                            </div>
                            <div class="rounded-xl border border-stone-200 bg-white p-3">
                                <p class="text-stone-500">Sudah Dibayar</p>
                                <p class="font-semibold text-stone-900">Rp {{ number_format($paid, 0, ',', '.') }}</p>
                            </div>
                            <div class="rounded-xl border border-stone-200 bg-white p-3">
                                <p class="text-stone-500">Sisa Bayar</p>
                                <p class="font-semibold {{ $remaining > 0 ? 'text-rose-700' : 'text-emerald-700' }}">Rp {{ number_format($remaining, 0, ',', '.') }}</p>
                            </div>
                            <div class="rounded-xl border border-stone-200 bg-white p-3">
                                <p class="text-stone-500">Metode</p>
                                <p class="font-semibold text-stone-900">{{ ucfirst($payment->payment_method) }}</p>
                            </div>
                        </div>

                        <div class="mt-4 space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                @if(! $isSettled && $payment->booking->status !== \App\Models\Booking::STATUS_CANCELED && ! $payment->booking->hasServicePassed())
                                    <form method="POST" action="{{ route('admin.payments.update-pricing', $payment) }}" class="inline-flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input
                                            type="number"
                                            name="discount_amount"
                                            min="0"
                                            max="{{ (int) max(0, floor($serviceSubtotal + $transportFee)) }}"
                                            step="1"
                                            value="{{ (int) round($discount) }}"
                                            class="w-28 rounded-lg border-stone-300 px-2 py-2 text-xs text-stone-700 focus:border-rose-400 focus:ring-rose-300"
                                            aria-label="Nominal Diskon"
                                        >
                                        <button type="submit" class="px-4 py-2 rounded-xl bg-stone-700 text-white text-sm hover:bg-stone-800">
                                            Simpan Diskon
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                @if(! $isDpPaid)
                                    <form method="POST" action="{{ route('admin.payments.mark-dp-paid', $payment) }}" class="inline-flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input
                                            type="number"
                                            name="dp_amount"
                                            min="0.01"
                                            max="{{ number_format((float) $total, 2, '.', '') }}"
                                            step="0.01"
                                            value="{{ number_format((float) $dp, 2, '.', '') }}"
                                            data-dp-input="true"
                                            class="w-32 rounded-lg border-stone-300 px-2 py-2 text-xs text-stone-700 focus:border-rose-400 focus:ring-rose-300"
                                            aria-label="Nominal DP"
                                        >
                                        <button
                                            type="button"
                                            class="px-3 py-2 rounded-xl border border-stone-300 text-stone-700 text-xs hover:bg-stone-50"
                                            onclick="this.previousElementSibling.value='{{ number_format((float) $total, 2, '.', '') }}';"
                                        >
                                            Lunas
                                        </button>
                                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 text-white text-sm hover:bg-amber-600">
                                            Bayar DP / Lunas
                                        </button>
                                    </form>
                                @endif

                                @if($isSettled)
                                    <span class="px-4 py-2 rounded-xl bg-emerald-100 text-emerald-700 text-sm font-semibold">
                                        Sudah Lunas
                                    </span>
                                @elseif($canSettle)
                                    <form method="POST" action="{{ route('admin.payments.mark-settled', $payment) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-500 text-white text-sm hover:bg-emerald-600">
                                            Pelunasan
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        @if($canCancelBooking)
                            <div class="mt-4 flex justify-end">
                                <form method="POST" action="{{ route('admin.payments.cancel-booking', $payment) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-red-500 text-white text-sm hover:bg-red-600">
                                        Batalkan Booking
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-stone-300 bg-stone-50 p-6 text-sm text-stone-500 text-center">
                        Belum ada data pembayaran.
                    </div>
                @endforelse
            </div>

            <div class="pt-2">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('input[data-dp-input="true"]').forEach((input) => {
            const clampValue = () => {
                const max = parseFloat(input.max || '0');
                const min = parseFloat(input.min || '0.01');
                const value = parseFloat(input.value || '0');
                if (Number.isNaN(value)) return;
                if (!Number.isNaN(max) && value > max) {
                    input.value = max.toFixed(2);
                    return;
                }
                if (!Number.isNaN(min) && value > 0 && value < min) {
                    input.value = min.toFixed(2);
                }
            };

            input.addEventListener('input', clampValue);
            input.closest('form')?.addEventListener('submit', clampValue);
        });
    });
</script>
