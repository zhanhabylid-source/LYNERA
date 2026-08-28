<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-stone-900">Layanan</h2>
                <p class="mt-1 text-sm text-stone-600">Kelola layanan dengan prioritas tampilan mobile.</p>
            </div>
            <a href="{{ route('admin.services.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700 min-h-[44px]" data-testid="service-add-button">
                + Tambah Layanan
            </a>
        </div>
    </x-slot>

    <div class="py-5 sm:py-8">
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->has('service'))
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first('service') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-2xl border border-rose-100 bg-white shadow-sm">
                <div class="space-y-3 p-4 sm:p-5 lg:hidden">
                    @forelse ($services as $service)
                        @php
                            $usageCount = (int) (($service->bookings_count ?? 0) + ($service->booking_items_count ?? 0));
                            $deletable = $usageCount === 0;
                        @endphp
                        <article class="rounded-xl border border-rose-100 bg-rose-50/30 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-base font-semibold text-stone-900">{{ $service->name }}</p>
                                    <p class="mt-1 text-sm text-stone-700">Rp {{ number_format((float) $service->price, 0, ',', '.') }}</p>
                                    <p class="text-sm text-stone-600">{{ (int) $service->duration }} menit</p>
                                </div>
                                @if ($usageCount > 0)
                                    <span class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                        Dipakai {{ $usageCount }}x
                                    </span>
                                @endif
                            </div>
                            <p class="mt-2 text-sm text-stone-600">{{ $service->description ?: '-' }}</p>
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <a href="{{ route('admin.services.edit', $service) }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-amber-500 px-3 py-2 text-sm font-semibold text-white hover:bg-amber-600 transition min-h-[44px]">
                                    Ubah
                                </a>
                                <form method="POST" action="{{ route('admin.services.destroy', $service) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="inline-flex min-h-11 w-full items-center justify-center rounded-lg px-3 py-2 text-sm font-semibold text-white transition min-h-[44px] {{ $deletable ? 'bg-rose-600 hover:bg-rose-700' : 'cursor-not-allowed bg-stone-300' }}"
                                        @disabled(! $deletable)
                                        onclick="return confirm('Hapus layanan ini?')"
                                    >
                                        Hapus
                                    </button>
                                </form>
                            </div>
                            @if (! $deletable)
                                <p class="mt-2 text-xs text-stone-500">Layanan yang sudah dipakai pada booking tidak bisa dihapus agar data riwayat tetap aman.</p>
                            @endif
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-rose-200 bg-rose-50/80 p-8 text-center">
                            <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl border border-rose-100 bg-white mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4" />
                                </svg>
                            </div>
                            <p class="font-semibold text-stone-900">Tambahkan layanan pertama Anda</p>
                            <p class="mt-2 text-sm text-stone-600">Mulai dengan menambahkan layanan makeup untuk menerima booking dari klien Anda.</p>
                            <a href="{{ route('admin.services.create') }}" class="mt-4 inline-flex rounded-2xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700 min-h-[44px] items-center">
                                Buat Layanan Pertama
                            </a>
                        </div>
                    @endforelse
                </div>

                <div class="hidden overflow-x-auto lg:block">
                    <table class="min-w-full divide-y divide-stone-200">
                        <thead class="bg-stone-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Nama</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Harga</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Durasi</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-stone-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-200 bg-white">
                            @forelse ($services as $service)
                                @php
                                    $usageCount = (int) (($service->bookings_count ?? 0) + ($service->booking_items_count ?? 0));
                                    $deletable = $usageCount === 0;
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-stone-900">{{ $service->name }}</td>
                                    <td class="px-4 py-3 text-sm text-stone-800">Rp {{ number_format((float) $service->price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-stone-700">{{ (int) $service->duration }} menit</td>
                                    <td class="px-4 py-3 text-sm text-stone-700">
                                        @if ($usageCount > 0)
                                            <span class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Dipakai {{ $usageCount }}x</span>
                                        @else
                                            <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Bisa dihapus</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.services.edit', $service) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-amber-500 px-3 py-1.5 text-sm font-semibold text-white hover:bg-amber-600">Ubah</a>
                                            <form method="POST" action="{{ route('admin.services.destroy', $service) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="inline-flex min-h-10 items-center justify-center rounded-lg px-3 py-1.5 text-sm font-semibold text-white {{ $deletable ? 'bg-rose-600 hover:bg-rose-700' : 'cursor-not-allowed bg-stone-300' }}"
                                                    @disabled(! $deletable)
                                                    onclick="return confirm('Hapus layanan ini?')"
                                                >
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center">
                                        <div class="inline-flex flex-col items-center gap-4 rounded-2xl border border-dashed border-rose-200 bg-rose-50/80 px-8 py-8 max-w-sm">
                                            <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl border border-rose-100 bg-white">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4" />
                                                </svg>
                                            </div>
                                            <div class="text-center space-y-2">
                                                <p class="text-base font-semibold text-stone-900">Tambahkan layanan pertama Anda</p>
                                                <p class="text-sm leading-6 text-stone-600">Mulai dengan menambahkan layanan makeup untuk menerima booking dan mengelola harga layanan Anda.</p>
                                            </div>
                                            <a href="{{ route('admin.services.create') }}" class="w-full rounded-2xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700">
                                                Buat Layanan Pertama
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-stone-100 p-4">
                    {{ $services->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

