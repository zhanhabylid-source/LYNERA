<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/png" href="{{ asset('images/logo/pavicon.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/logo/pavicon.png') }}">

        <title>{{ config('app.name', 'LYNERA') }} | {{ config('app.tagline', 'Smart Tools for Modern Makeup Artists') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @include('partials.vite-assets')
    </head>
    <body class="font-sans text-stone-900 antialiased">
        <div class="min-h-screen relative overflow-hidden">
            {{-- <div class="absolute inset-0 bg-gradient-to-br from-rose-100 via-amber-50 to-stone-100"></div>
            <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-rose-200/40 blur-3xl"></div>
            <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full bg-amber-200/40 blur-3xl"></div> --}}

            <div class="relative min-h-screen py-8 sm:py-10">
                {{-- <div class="mx-auto grid min-h-[calc(100vh-4rem)] w-full max-w-6xl items-center gap-6 px-4 sm:px-6 lg:grid-cols-1 lg:gap-10 lg:px-8"> --}}
                    {{-- <div class="hidden rounded-3xl border border-rose-100 bg-white/70 p-8 shadow-xl backdrop-blur lg:block">
                        <a href="/" class="inline-flex items-center gap-3">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white shadow-md border border-rose-100">
                                <x-application-logo class="h-7 w-auto" />
                            </span>
                            <span class="text-xl font-semibold tracking-tight text-stone-800">{{ config('app.name', 'LYNERA') }}</span>
                        </a>
                        <p class="mt-2 text-xs uppercase tracking-wide text-rose-600">{{ config('app.tagline', 'Smart Tools for Modern Makeup Artists') }}</p>
                        <h2 class="mt-6 text-3xl font-semibold leading-tight text-stone-900">Kelola Bisnis Makeup Lebih Cepat</h2>
                        <p class="mt-3 text-sm leading-6 text-stone-600">Booking, pelanggan, pembayaran, dan jadwal terpusat dalam satu dashboard yang nyaman dipakai dari layar desktop sampai mobile.</p>
                        <div class="mt-6 grid gap-3 text-sm text-stone-700">
                            <div class="rounded-xl border border-rose-100 bg-white p-3">Kalender booking real-time</div>
                            <div class="rounded-xl border border-rose-100 bg-white p-3">Laporan pendapatan praktis</div>
                            <div class="rounded-xl border border-rose-100 bg-white p-3">Akses publik via booking link</div>
                        </div>
                        <a href="{{ route('landing.home') }}" class="mt-3 inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:bg-stone-50">
                            More Information
                        </a>
                    </div> --}}

                    {{-- <div class="w-full max-w-full justify-self-center lg:justify-self-end"> --}}
                    {{-- <div class="mx-auto grid min-h-[calc(100vh-4rem)] w-full max-w-6xl items-center gap-6 px-4 sm:px-6 lg:grid-cols-1 lg:gap-10 lg:px-8"> --}}
                        <div class="mb-5 text-center lg:hidden">
                            <a href="/" class="inline-flex items-center gap-3">
                                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white shadow-md border border-rose-100">
                                    <x-application-logo class="h-6 w-auto" />
                                </span>
                                <span class="text-lg font-semibold tracking-tight text-stone-800">{{ config('app.name', 'LYNERA') }}</span>
                            </a>
                            <p class="mt-2 text-xs uppercase tracking-wide text-rose-600">{{ config('app.tagline', 'Smart Tools for Modern Makeup Artists') }}</p>
                        </div>
                        <div class="w-full rounded-2xl bg-white/95 p-5 sm:p-8">
                            {{ $slot }}
                        </div>
                    {{-- </div> --}}
                {{-- </div> --}}
            </div>
        </div>
    </body>
</html>

