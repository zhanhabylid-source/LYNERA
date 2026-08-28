<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-stone-800 leading-tight">Laporan & Analitik</h2>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-rose-50 via-amber-50 to-white min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @unless($hasAdvancedReports)
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-800">
                    Analitik lanjutan tersedia untuk paket Pro dan Premium. Upgrade paket jika Anda ingin insight bisnis yang lebih mendalam.
                </div>
            @endunless

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 md:gap-5">
                <div class="rounded-xl md:rounded-2xl p-4 md:p-6 border border-rose-100 bg-rose-50/40 md:bg-white shadow-sm md:shadow" data-testid="report-revenue-total">
                    <p class="text-xs md:text-sm text-stone-500">Total Uang Diterima</p>
                    <p class="mt-2 text-xl md:text-2xl font-bold text-stone-900">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                    <p class="mt-1 text-[11px] md:text-xs text-stone-500">Termasuk DP + pelunasan</p>
                </div>
                <div class="rounded-xl md:rounded-2xl p-4 md:p-6 border border-rose-100 bg-rose-50/40 md:bg-white shadow-sm md:shadow" data-testid="report-revenue-monthly">
                    <p class="text-xs md:text-sm text-stone-500">Pendapatan Bulan Ini</p>
                    <p class="mt-2 text-xl md:text-2xl font-bold text-stone-900">Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}</p>
                    <p class="mt-1 text-[11px] md:text-xs text-stone-500">Termasuk DP + pelunasan</p>
                </div>
                <div class="rounded-xl md:rounded-2xl p-4 md:p-6 border border-rose-100 bg-rose-50/40 md:bg-white shadow-sm md:shadow">
                    <p class="text-xs md:text-sm text-stone-500">Total Booking</p>
                    <p class="mt-2 text-xl md:text-2xl font-bold text-stone-900">{{ $totalBookings }}</p>
                </div>
                <div class="rounded-xl md:rounded-2xl p-4 md:p-6 border border-rose-100 bg-rose-50/40 md:bg-white shadow-sm md:shadow">
                    <p class="text-xs md:text-sm text-stone-500">Rasio Konversi</p>
                    <p class="mt-2 text-xl md:text-2xl font-bold text-stone-900">{{ number_format($conversionRate, 2) }}%</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-rose-100 shadow">
                <h3 class="text-lg font-semibold text-stone-900">Funnel Pembayaran</h3>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                        <p class="text-sm text-emerald-700">Pembayaran Lunas</p>
                        <p class="mt-2 text-2xl md:text-3xl font-bold text-emerald-800">{{ $paidCount }}</p>
                    </div>
                    <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4">
                        <p class="text-sm text-yellow-700">Menunggu Pembayaran</p>
                        <p class="mt-2 text-2xl md:text-3xl font-bold text-yellow-800">{{ $pendingCount }}</p>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    <a href="{{ route('admin.payments.index') }}" class="px-4 py-2 rounded-xl bg-rose-500 text-white text-sm hover:bg-rose-600 transition">
                        Bayar
                    </a>
                    <a href="{{ route('admin.bookings.index') }}" class="px-4 py-2 rounded-xl border border-stone-300 text-stone-700 text-sm hover:bg-stone-50 transition">
                        Booking
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

