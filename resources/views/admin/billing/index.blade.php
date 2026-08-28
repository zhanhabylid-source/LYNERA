<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-stone-800 leading-tight">Tagihan</h2>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-rose-50 via-amber-50 to-white min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('error'))
                <div class="p-4 rounded-xl border border-red-200 bg-red-50 text-red-700">{{ session('error') }}</div>
            @endif

            <div class="bg-white rounded-2xl border border-rose-100 shadow p-6">
                <h3 class="text-lg font-semibold text-stone-900">Langganan Saat Ini</h3>
                <div class="mt-4 grid md:grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl bg-rose-50/40 border border-rose-100">
                        <p class="text-sm text-stone-500">Paket</p>
                        <p class="text-xl font-bold text-stone-900 uppercase">{{ $subscription?->plan ?? 'free' }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-rose-50/40 border border-rose-100">
                        <p class="text-sm text-stone-500">Masa Aktif</p>
                        <p class="text-xl font-bold text-stone-900">
                            Tanpa batas waktu
                        </p>
                    </div>
                </div>

                <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    Upgrade paket akan membantu membuka limit booking dan fitur premium untuk operasional harian.
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('landing.pricing') }}" class="px-6 py-2.5 rounded-xl bg-rose-500 text-white hover:bg-rose-600">
                        Upgrade
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="px-6 py-2.5 rounded-xl border border-stone-300 text-stone-700 hover:bg-stone-50">
                        Dasbor
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


