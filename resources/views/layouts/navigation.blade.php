@php
    $navUser = auth()->user();
    $tomorrowDate = now()->addDay()->toDateString();
    $notificationsEnabled = (bool) ($navUser?->notify_tomorrow_booking ?? true);
    $tomorrowBookingCount = 0;
    if ($navUser && $notificationsEnabled) {
        $countCacheKey = 'nav:tomorrow-bookings:'.$navUser->id.':'.$tomorrowDate;
        $tomorrowBookingCount = \Illuminate\Support\Facades\Cache::remember($countCacheKey, now()->addSeconds(30), function () use ($navUser, $tomorrowDate) {
            return \App\Models\Booking::withoutGlobalScopes()
                ->where('tenant_id', $navUser->id)
                ->whereDate('booking_date', $tomorrowDate)
                ->whereIn('status', [\App\Models\Booking::STATUS_PENDING, \App\Models\Booking::STATUS_CONFIRMED])
                ->count();
        });
    }
    $planKey = getUserPlan($navUser);
    $planName = strtoupper((string) (config("plans.plans.$planKey.name") ?? $planKey));
    $planBadgeClass = match ($planKey) {
        'premium' => 'border-amber-200 bg-amber-50 text-amber-700',
        'pro' => 'border-rose-200 bg-rose-50 text-rose-700',
        default => 'border-stone-200 bg-stone-50 text-stone-700',
    };
    $planDividerClass = match ($planKey) {
        'premium' => 'text-amber-400',
        'pro' => 'text-rose-400',
        default => 'text-stone-400',
    };
    $usage = null;
    if ($navUser) {
        $usageCacheKey = 'nav:booking-usage:'.$navUser->id;
        $usage = \Illuminate\Support\Facades\Cache::remember($usageCacheKey, now()->addSeconds(30), function () use ($navUser) {
            return app(\App\Services\SubscriptionService::class)->getBookingUsage((int) $navUser->id);
        });
    }
    $usageLabel = $usage
        ? ($usage['is_unlimited']
            ? 'Kuota: Tanpa batas'
            : 'Kuota: '.$usage['bookings_count'].'/'.$usage['limit'])
        : 'Kuota: -';
    $isNearLimit = $usage
        && ! $usage['is_unlimited']
        && (int) ($usage['limit'] ?? 0) > 0
        && (int) $usage['bookings_count'] >= max(1, (int) ceil(((int) $usage['limit']) * 0.8));
    $profileLogoUrl = $navUser?->logoUrl();
    $profileInitial = strtoupper(substr((string) ($navUser?->name ?? 'U'), 0, 1));
@endphp

<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-rose-100 bg-white/95 backdrop-blur">
    <!-- Primary Navigation Menu -->
    <div class="content-container">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                        Dasbor
                    </x-nav-link>
                    <x-nav-link :href="route('admin.services.index')" :active="request()->routeIs('admin.services.*')">
                        {{ __('Layanan') }}
                    </x-nav-link>
                    <x-nav-link :href="route('admin.customers.index')" :active="request()->routeIs('admin.customers.*')">
                        {{ __('Pelanggan') }}
                    </x-nav-link>
                    <x-nav-link :href="route('admin.bookings.index')" :active="request()->routeIs('admin.bookings.*')">
                        Booking
                    </x-nav-link>
                    <x-nav-link :href="route('billing.index')" :active="request()->routeIs('billing.*')">
                        Tagihan
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-2">
                @if(auth()->user()?->isSuperAdmin())
                    <a
                        href="{{ route('backend.dashboard') }}"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-stone-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-black"
                        data-testid="back-to-admin-panel"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        Panel Admin
                    </a>
                @endif
                <a
                    href="{{ route('admin.calendar.index') }}"
                    title="{{ $tomorrowBookingCount > 0 ? 'Ada '.$tomorrowBookingCount.' booking untuk besok' : 'Buka Kalender Booking' }}"
                    aria-label="{{ $tomorrowBookingCount > 0 ? 'Ada '.$tomorrowBookingCount.' booking untuk besok. Buka Kalender Booking' : 'Buka Kalender Booking' }}"
                    class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-rose-100 bg-white text-rose-600 transition hover:bg-rose-50 hover:text-rose-700 {{ request()->routeIs('admin.calendar.*') ? 'ring-2 ring-rose-300' : '' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M6.75 2.25a.75.75 0 0 1 .75.75v1.5h9V3a.75.75 0 0 1 1.5 0v1.5h.75A2.25 2.25 0 0 1 21 6.75v12A2.25 2.25 0 0 1 18.75 21h-13.5A2.25 2.25 0 0 1 3 18.75v-12A2.25 2.25 0 0 1 5.25 4.5H6V3a.75.75 0 0 1 .75-.75ZM4.5 9v9.75c0 .414.336.75.75.75h13.5a.75.75 0 0 0 .75-.75V9h-15Z" />
                    </svg>
                    @if($tomorrowBookingCount > 0)
                        <span class="absolute -top-1 -right-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">
                            {{ $tomorrowBookingCount > 9 ? '9+' : $tomorrowBookingCount }}
                        </span>
                    @endif
                </a>
                <div class="inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-xs font-semibold {{ $planBadgeClass }}">
                    <span>Paket {{ $planName }}</span>
                    <span class="{{ $planDividerClass }}">|</span>
                    <span>{{ $usageLabel }}</span>
                    @if($isNearLimit)
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-700">
                            Hampir Habis
                        </span>
                    @endif
                </div>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 rounded-xl border border-rose-100 bg-white px-3 py-2 text-sm font-medium leading-4 text-stone-700 hover:bg-rose-50 focus:outline-none transition">
                            <span class="inline-flex h-8 w-8 overflow-hidden rounded-full border border-stone-200 bg-stone-50">
                                @if($profileLogoUrl)
                                    <img src="{{ $profileLogoUrl }}" alt="Logo profil" class="h-full w-full object-cover">
                                @else
                                    <span class="inline-flex h-full w-full items-center justify-center text-xs font-bold text-stone-600">{{ $profileInitial }}</span>
                                @endif
                            </span>
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            Profil
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                Keluar
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-xl p-3 text-stone-500 hover:bg-rose-50 hover:text-rose-600 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-rose-100 bg-white sm:hidden">
        <div class="content-container space-y-2 py-3">
            @if(auth()->user()?->isSuperAdmin())
                <a href="{{ route('backend.dashboard') }}" class="flex items-center gap-2 rounded-xl bg-stone-900 px-3 py-2 text-sm font-semibold text-white" data-testid="back-to-admin-panel-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Kembali ke Panel Admin
                </a>
            @endif
            <div class="rounded-xl border px-3 py-2 text-xs font-semibold {{ $planBadgeClass }}">
                <div class="flex flex-wrap items-center gap-2">
                    <span>Paket {{ $planName }} | {{ $usageLabel }}</span>
                    @if($isNearLimit)
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-700">
                            Hampir Habis
                        </span>
                    @endif
                </div>
            </div>
            @if($tomorrowBookingCount > 0)
                <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700">
                    Notifikasi: Besok ada {{ $tomorrowBookingCount }} booking.
                </div>
            @endif
            <x-responsive-nav-link :href="route('admin.calendar.index')" :active="request()->routeIs('admin.calendar.*')">
                <span class="inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M6.75 2.25a.75.75 0 0 1 .75.75v1.5h9V3a.75.75 0 0 1 1.5 0v1.5h.75A2.25 2.25 0 0 1 21 6.75v12A2.25 2.25 0 0 1 18.75 21h-13.5A2.25 2.25 0 0 1 3 18.75v-12A2.25 2.25 0 0 1 5.25 4.5H6V3a.75.75 0 0 1 .75-.75ZM4.5 9v9.75c0 .414.336.75.75.75h13.5a.75.75 0 0 0 .75-.75V9h-15Z" />
                    </svg>
                    Kalender Booking
                </span>
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                Dasbor
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.services.index')" :active="request()->routeIs('admin.services.*')">
                {{ __('Layanan') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.customers.index')" :active="request()->routeIs('admin.customers.*')">
                {{ __('Pelanggan') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.bookings.index')" :active="request()->routeIs('admin.bookings.*')">
                Booking
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('billing.index')" :active="request()->routeIs('billing.*')">
                Tagihan
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="border-t border-rose-100 py-3">
            <div class="content-container">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 overflow-hidden rounded-full border border-stone-200 bg-stone-50">
                        @if($profileLogoUrl)
                            <img src="{{ $profileLogoUrl }}" alt="Logo profil" class="h-full w-full object-cover">
                        @else
                            <span class="inline-flex h-full w-full items-center justify-center text-xs font-bold text-stone-600">{{ $profileInitial }}</span>
                        @endif
                    </span>
                    <div>
                        <div class="font-medium text-sm text-stone-800">{{ Auth::user()->name }}</div>
                        <div class="font-medium text-xs text-stone-500">{{ Auth::user()->email }}</div>
                    </div>
                </div>
            </div>

            <div class="content-container mt-3 space-y-2">
                <x-responsive-nav-link :href="route('profile.edit')">
                    Profil
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        Keluar
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>


