<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pelanggan</h2>
            <div class="flex items-center gap-2">
                @if(auth()->user()->subscriptionUpgradeRequests->where('status', 'pending')->count() > 0)
                    <span class="rounded-full border border-orange-200 bg-orange-50 px-3 py-1 text-sm font-semibold text-orange-700">Request Upgrade ke Layanan</span>
                @endif
                <a href="{{ route('admin.customers.create') }}" class="px-4 py-2 bg-rose-600 text-white rounded-md hover:bg-rose-700 transition min-h-[44px] flex items-center">
                    Tambah
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-3 rounded-md bg-emerald-100 text-emerald-700">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-3 rounded-md bg-rose-100 text-rose-700">{{ session('error') }}</div>
            @endif

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telepon</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Instagram</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($customers as $customer)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $customer->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $customer->phone }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $customer->email ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $customer->instagram ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.customers.edit', $customer) }}" class="px-3 py-1.5 text-sm bg-amber-500 text-white rounded hover:bg-amber-600">Ubah</a>
                                            @if(($customer->bookings_count ?? 0) > 0)
                                                <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold bg-stone-200 text-stone-700 rounded border border-stone-300" title="Tidak bisa dihapus karena masih dipakai di booking" data-testid="customer-usage-badge">
                                                    <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m0-10a2 2 0 11-4 0 2 2 0 014 0zm-6 4a6 6 0 1112 0v3a6 6 0 11-12 0v-3z"/></svg>
                                                    Dipakai {{ $customer->bookings_count }}x
                                                </span>
                                            @else
                                                <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded hover:bg-red-700" onclick="return confirm('Hapus pelanggan ini?')">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center">
                                        <div class="inline-flex flex-col items-center gap-4 rounded-2xl border border-dashed border-rose-200 bg-rose-50/80 px-8 py-8 max-w-sm">
                                            <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl border border-rose-100 bg-white">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM6 20a7 7 0 1114 0" />
                                                </svg>
                                            </div>
                                            <div class="text-center space-y-2">
                                                <p class="text-base font-semibold text-stone-900">Mulai bangun daftar klien Anda</p>
                                                <p class="text-sm leading-6 text-stone-600">Tambahkan pelanggan pertama Anda untuk mulai mengelola booking dan riwayat layanan mereka dengan lebih mudah.</p>
                                            </div>
                                            <a href="{{ route('admin.customers.create') }}" class="w-full rounded-2xl bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-600">
                                                Tambah Pelanggan Pertama
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="md:hidden p-4 space-y-3">
                    @forelse ($customers as $customer)
                        <div class="rounded-xl border border-rose-100 bg-rose-50/40 p-4 shadow-sm">
                            <p class="text-sm font-semibold text-stone-900">{{ $customer->name }}</p>
                            <p class="mt-1 text-sm text-stone-700">{{ $customer->phone }}</p>
                            <p class="text-sm text-stone-600">{{ $customer->email ?? '-' }}</p>
                            <p class="text-sm text-stone-600">{{ $customer->instagram ?? '-' }}</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <a href="{{ route('admin.customers.edit', $customer) }}" class="px-3 py-1.5 text-sm bg-amber-500 text-white rounded-lg hover:bg-amber-600">Ubah</a>
                                @if(($customer->bookings_count ?? 0) > 0)
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold bg-stone-200 text-stone-700 rounded-lg border border-stone-300" title="Masih dipakai di booking" data-testid="customer-usage-badge">
                                        Dipakai {{ $customer->bookings_count }}x
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700" onclick="return confirm('Hapus pelanggan ini?')">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-rose-200 bg-rose-50/80 p-8 text-center">
                            <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl border border-rose-100 bg-white mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM6 20a7 7 0 1114 0" />
                                </svg>
                            </div>
                            <p class="font-semibold text-stone-900">Mulai bangun daftar klien Anda</p>
                            <p class="mt-2 text-sm text-stone-600">Tambahkan pelanggan pertama untuk mengelola booking dan layanan mereka.</p>
                            <a href="{{ route('admin.customers.create') }}" class="mt-4 inline-flex rounded-2xl bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-600">
                                Tambah Pelanggan
                            </a>
                        </div>
                    @endforelse
                </div>
                <div class="p-4">
                    {{ $customers->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


