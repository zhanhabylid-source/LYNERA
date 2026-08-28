<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'LYNERA') }} | Backend</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @include('partials.vite-assets')
        <style>[x-cloak]{display:none!important;}</style>
    </head>
    <body class="bg-stone-100 font-sans text-stone-900 antialiased">
        @php
            $pendingUpgradeCount = \App\Models\SubscriptionUpgradeRequest::query()
                ->where('status', \App\Models\SubscriptionUpgradeRequest::STATUS_PENDING_VERIFICATION)
                ->count();
        @endphp
        <header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-stone-200 bg-white/95 backdrop-blur">
            <div class="mx-auto w-full max-w-7xl px-4 py-3 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-rose-100 bg-white">
                            <x-application-logo class="h-7 w-7 object-contain" data-testid="backend-application-logo" />
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-rose-600">Backend</p>
                            <h1 class="truncate text-sm font-semibold text-stone-900 sm:text-lg">Super Admin Panel</h1>
                        </div>
                    </div>

                    <!-- Desktop nav -->
                    <nav class="hidden items-center gap-2 text-sm lg:flex">
                        <a href="{{ route('backend.dashboard') }}" class="rounded-lg px-3 py-2 font-medium {{ request()->routeIs('backend.dashboard') ? 'bg-stone-900 text-white' : 'bg-white text-stone-700 hover:bg-stone-100' }}" data-testid="backend-dashboard-link">Dashboard</a>
                        <a href="{{ route('backend.tenants.index') }}" class="relative rounded-lg px-3 py-2 font-medium {{ request()->routeIs('backend.tenants.*') ? 'bg-stone-900 text-white' : 'bg-white text-stone-700 hover:bg-stone-100' }}" data-testid="nav-tenant-link">
                            Tenant
                            @if($pendingUpgradeCount > 0)
                                <span class="ml-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-rose-600 px-1.5 text-[11px] font-bold text-white" data-testid="nav-pending-upgrade-badge-desktop">{{ $pendingUpgradeCount > 99 ? '99+' : $pendingUpgradeCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('backend.plans.index') }}" class="rounded-lg px-3 py-2 font-medium {{ request()->routeIs('backend.plans.*') ? 'bg-stone-900 text-white' : 'bg-white text-stone-700 hover:bg-stone-100' }}" data-testid="backend-plans-link">Paket</a>
                        <a href="{{ route('backend.payment-methods.index') }}" class="rounded-lg px-3 py-2 font-medium {{ request()->routeIs('backend.payment-methods.*') ? 'bg-stone-900 text-white' : 'bg-white text-stone-700 hover:bg-stone-100' }}" data-testid="backend-payment-methods-link">Pembayaran</a>
                        <a href="{{ route('backend.audit-logs.index') }}" class="rounded-lg px-3 py-2 font-medium {{ request()->routeIs('backend.audit-logs.*') ? 'bg-stone-900 text-white' : 'bg-white text-stone-700 hover:bg-stone-100' }}" data-testid="backend-audit-log-link">Audit Log</a>
                        <a href="{{ route('backend.backup.index') }}" class="rounded-lg px-3 py-2 font-medium {{ request()->routeIs('backend.backup.*') ? 'bg-stone-900 text-white' : 'bg-white text-stone-700 hover:bg-stone-100' }}" data-testid="backend-backup-link">Backup</a>
                        <a href="{{ route('admin.dashboard') }}" class="rounded-lg border border-stone-200 px-3 py-2 font-medium text-stone-700 hover:bg-stone-100" data-testid="backend-frontend-link">Frontend</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 font-medium text-rose-700 hover:bg-rose-100" data-testid="backend-logout-button">Keluar</button>
                        </form>
                    </nav>

                    <!-- Mobile hamburger -->
                    <button
                        type="button"
                        @click="open = !open"
                        class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-stone-200 bg-white text-stone-700 hover:bg-stone-100 lg:hidden"
                        aria-label="Buka menu"
                        data-testid="backend-mobile-menu-button"
                    >
                        <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        @if($pendingUpgradeCount > 0)
                            <span class="absolute -right-1 -top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-bold text-white" data-testid="nav-pending-upgrade-badge-mobile-trigger">{{ $pendingUpgradeCount > 99 ? '99+' : $pendingUpgradeCount }}</span>
                        @endif
                    </button>
                </div>

                <!-- Mobile menu panel -->
                <nav x-show="open" x-cloak x-transition class="mt-3 space-y-1 border-t border-stone-200 pt-3 text-sm lg:hidden" data-testid="backend-mobile-menu">
                    <a href="{{ route('backend.dashboard') }}" class="block w-full rounded-xl px-3 py-3 font-medium {{ request()->routeIs('backend.dashboard') ? 'bg-rose-100 text-rose-700 ring-1 ring-rose-200' : 'text-stone-700 hover:bg-rose-50 hover:text-rose-700' }}" data-testid="backend-mobile-dashboard-link">Dashboard</a>
                    <a href="{{ route('backend.tenants.index') }}" class="flex w-full items-center justify-between rounded-xl px-3 py-3 font-medium {{ request()->routeIs('backend.tenants.*') ? 'bg-rose-100 text-rose-700 ring-1 ring-rose-200' : 'text-stone-700 hover:bg-rose-50 hover:text-rose-700' }}" data-testid="backend-mobile-tenant-link">
                        <span>Tenant</span>
                        @if($pendingUpgradeCount > 0)
                            <span class="inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-rose-600 px-1.5 text-[11px] font-bold text-white" data-testid="nav-pending-upgrade-badge">{{ $pendingUpgradeCount > 99 ? '99+' : $pendingUpgradeCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('backend.plans.index') }}" class="block w-full rounded-xl px-3 py-3 font-medium {{ request()->routeIs('backend.plans.*') ? 'bg-rose-100 text-rose-700 ring-1 ring-rose-200' : 'text-stone-700 hover:bg-rose-50 hover:text-rose-700' }}" data-testid="backend-mobile-plans-link">Paket</a>
                    <a href="{{ route('backend.payment-methods.index') }}" class="block w-full rounded-xl px-3 py-3 font-medium {{ request()->routeIs('backend.payment-methods.*') ? 'bg-rose-100 text-rose-700 ring-1 ring-rose-200' : 'text-stone-700 hover:bg-rose-50 hover:text-rose-700' }}" data-testid="backend-mobile-payment-methods-link">Pembayaran</a>
                    <a href="{{ route('backend.audit-logs.index') }}" class="block w-full rounded-xl px-3 py-3 font-medium {{ request()->routeIs('backend.audit-logs.*') ? 'bg-rose-100 text-rose-700 ring-1 ring-rose-200' : 'text-stone-700 hover:bg-rose-50 hover:text-rose-700' }}" data-testid="backend-mobile-audit-log-link">Audit Log</a>
                    <a href="{{ route('backend.backup.index') }}" class="block w-full rounded-xl px-3 py-3 font-medium {{ request()->routeIs('backend.backup.*') ? 'bg-rose-100 text-rose-700 ring-1 ring-rose-200' : 'text-stone-700 hover:bg-rose-50 hover:text-rose-700' }}" data-testid="backend-mobile-backup-link">Backup</a>
                    <a href="{{ route('admin.dashboard') }}" class="block w-full rounded-xl px-3 py-3 font-medium text-stone-700 hover:bg-rose-50 hover:text-rose-700" data-testid="backend-mobile-frontend-link">Frontend (Tampilan Tenant)</a>
                    <form method="POST" action="{{ route('logout') }}" class="border-t border-stone-200 pt-3">
                        @csrf
                        <button type="submit" class="w-full rounded-xl border border-rose-200 bg-rose-50 px-3 py-3 text-left font-semibold text-rose-700 hover:bg-rose-100" data-testid="backend-mobile-logout-button">Keluar</button>
                    </form>
                </nav>
            </div>
        </header>

        <main class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            @if (session('success'))
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 4000)"
                    x-show="show"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed right-4 top-4 z-[60] flex max-w-sm items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 shadow-lg"
                    data-testid="toast-success"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 flex-shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    <span>{{ session('success') }}</span>
                    <button type="button" @click="show = false" class="ml-1 text-emerald-600 hover:text-emerald-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700" data-testid="flash-error">
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <p class="font-semibold">Ada data yang belum valid.</p>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            {{ $slot }}
        </main>
    </body>
</html>

