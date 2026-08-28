<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-stone-800 leading-tight">Profil Tenant</h2>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-rose-50 via-amber-50 to-white min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Paket Aktif</p>
                    <p class="mt-2 text-xl font-bold text-stone-900">{{ strtoupper($planKey) }}</p>
                    <p class="mt-1 text-sm text-stone-600">{{ $planDetail['booking_limit_label'] ?? '-' }}</p>
                </article>
                <article class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Booking Terpakai</p>
                    <p class="mt-2 text-xl font-bold text-stone-900">{{ $bookingUsage['bookings_count'] }}</p>
                    <p class="mt-1 text-sm text-stone-600">
                        @if($bookingUsage['is_unlimited'])
                            Tanpa batas
                        @else
                            Sisa {{ $bookingUsage['remaining'] }} dari {{ $bookingUsage['limit'] }}
                        @endif
                    </p>
                </article>
                <article class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Layanan</p>
                    <p class="mt-2 text-xl font-bold text-stone-900">{{ $user->services_count }}</p>
                    <a href="{{ route('admin.services.index') }}" class="mt-1 inline-block text-sm font-medium text-rose-600 hover:text-rose-700">Kelola layanan</a>
                </article>
                <article class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Pelanggan</p>
                    <p class="mt-2 text-xl font-bold text-stone-900">{{ $user->customers_count }}</p>
                    <a href="{{ route('admin.customers.index') }}" class="mt-1 inline-block text-sm font-medium text-rose-600 hover:text-rose-700">Kelola pelanggan</a>
                </article>
            </section>

            <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm sm:p-6">
                <div class="max-w-3xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            @include('profile.partials.payment-methods')

            <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="max-w-2xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="rounded-2xl border border-rose-200 bg-rose-50/50 p-5 shadow-sm sm:p-6">
                <div class="max-w-2xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
