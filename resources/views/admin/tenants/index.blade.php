<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-stone-800 leading-tight">Manajemen Tenant</h2>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-rose-50 via-amber-50 to-white min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-rose-100 shadow overflow-hidden">
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-200">
                        <thead class="bg-stone-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase">Tenant</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase">Peran</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase">Paket</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase">Layanan</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase">Pelanggan</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase">Booking</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse($tenants as $tenant)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-stone-800">{{ $tenant->name }} ({{ $tenant->email }})</td>
                                    <td class="px-4 py-3 text-sm text-stone-700">{{ $tenant->role }}</td>
                                    <td class="px-4 py-3 text-sm text-stone-700">{{ $tenant->subscription?->plan ?? 'free' }}</td>
                                    <td class="px-4 py-3 text-sm text-stone-700">{{ $tenant->services_count }}</td>
                                    <td class="px-4 py-3 text-sm text-stone-700">{{ $tenant->customers_count }}</td>
                                    <td class="px-4 py-3 text-sm text-stone-700">{{ $tenant->bookings_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-stone-500">Tidak ada tenant ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="md:hidden p-4 space-y-3">
                    @forelse($tenants as $tenant)
                        <div class="rounded-xl border border-rose-100 bg-rose-50/40 p-4 shadow-sm">
                            <p class="text-sm font-semibold text-stone-900">{{ $tenant->name }}</p>
                            <p class="text-sm text-stone-600">{{ $tenant->email }}</p>
                            <div class="mt-2 grid grid-cols-2 gap-2 text-sm text-stone-700">
                                <p>Peran: {{ $tenant->role }}</p>
                                <p>Paket: {{ $tenant->subscription?->plan ?? 'free' }}</p>
                                <p>Layanan: {{ $tenant->services_count }}</p>
                                <p>Pelanggan: {{ $tenant->customers_count }}</p>
                                <p>Booking: {{ $tenant->bookings_count }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-stone-300 bg-stone-50 p-4 text-sm text-stone-500">
                            Tidak ada tenant ditemukan.
                        </div>
                    @endforelse
                </div>
                <div class="p-4">{{ $tenants->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>


