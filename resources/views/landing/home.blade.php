<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'LYNERA') }} | {{ config('app.tagline', 'Smart Tools for Modern Makeup Artists') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&family=playfair-display:600,700&display=swap" rel="stylesheet" />
    @include('partials.vite-assets')
    <style>
        :root {
            --ink: #1f2937;
            --muted: #4b5563;
            --brand: #b45309;
            --brand-deep: #7c2d12;
            --line: rgba(146, 64, 14, 0.22);
            --surface: rgba(255, 255, 255, 0.8);
        }

        .font-display {
            font-family: "Playfair Display", Georgia, serif;
        }

        .font-body {
            font-family: "Manrope", ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
        }

        .premium-grid {
            background-image: linear-gradient(to right, rgba(124, 45, 18, 0.04) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(124, 45, 18, 0.04) 1px, transparent 1px);
            background-size: 34px 34px;
        }
    </style>
</head>
<body class="relative overflow-x-hidden font-body bg-gradient-to-br from-amber-50 via-orange-50 to-stone-100 text-[var(--ink)]">
    <div class="pointer-events-none absolute inset-0 premium-grid"></div>
    <div class="pointer-events-none absolute -top-28 left-[-5rem] h-72 w-72 rounded-full bg-amber-200/45 blur-3xl"></div>
    <div class="pointer-events-none absolute bottom-[-6rem] right-[-4rem] h-80 w-80 rounded-full bg-orange-200/40 blur-3xl"></div>

    <header class="relative mx-auto max-w-7xl px-4 py-4 sm:px-6 sm:py-6">
        <div class="flex flex-col gap-3 rounded-2xl border border-[var(--line)] bg-[var(--surface)] px-4 py-3 shadow-sm backdrop-blur sm:flex-row sm:items-center sm:justify-between md:px-6 md:py-4">
            <a href="{{ route('landing.home') }}" class="text-lg font-extrabold tracking-tight text-[var(--brand-deep)] sm:text-xl">{{ config('app.name', 'LYNERA') }}</a>
            <nav class="flex w-full flex-wrap items-center gap-2 text-sm font-semibold sm:w-auto sm:justify-end sm:gap-4">
                <a href="{{ route('landing.features') }}" class="text-[var(--muted)] hover:text-[var(--brand-deep)]">Fitur</a>
                <a href="{{ route('landing.pricing') }}" class="text-[var(--muted)] hover:text-[var(--brand-deep)]">Harga</a>
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="min-h-11 flex-1 rounded-xl border border-amber-700/20 bg-amber-700 px-4 py-2 text-center text-white shadow-sm hover:bg-amber-800 sm:flex-none">Masuk Dasbor</a>
                @else
                    <a href="{{ route('login') }}" class="min-h-11 flex-1 rounded-xl border border-amber-700/20 bg-white px-4 py-2 text-center text-[var(--brand-deep)] shadow-sm hover:bg-amber-50 sm:flex-none">Login</a>
                    <a href="{{ route('register') }}" class="min-h-11 flex-1 rounded-xl border border-amber-700/20 bg-amber-700 px-4 py-2 text-center text-white shadow-sm hover:bg-amber-800 sm:flex-none">Coba Gratis</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="relative mx-auto max-w-7xl px-4 pb-14 sm:px-6 md:pb-24">
        <section class="grid gap-8 pt-6 sm:gap-10 md:pt-10 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
            <div>
                <p class="inline-flex items-center rounded-full border border-amber-700/20 bg-white/80 px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-[var(--brand)] sm:px-4 sm:text-xs sm:tracking-[0.22em]">
                    Platform Manajemen MUA Modern
                </p>
                <h1 class="font-display mt-5 text-3xl leading-tight text-[var(--brand-deep)] sm:text-4xl md:mt-6 md:text-5xl lg:text-6xl">
                    Pengalaman mengelola layanan makeup yang terasa mudah, rapi, dan premium.
                </h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-[var(--muted)] sm:text-base md:mt-5 md:text-lg">
                    Semua proses usaha kamu ada di satu tempat: jadwal terhubung kalender, notifikasi pengingat otomatis,
                    laporan performa yang jelas, dan pengelolaan layanan yang benar-benar worth untuk scale bisnis.
                </p>
                <div class="mt-7 flex flex-col items-stretch gap-3 sm:mt-8 sm:flex-row sm:flex-wrap sm:items-center">
                    <a href="{{ route('register') }}" class="min-h-11 rounded-xl bg-amber-700 px-6 py-3 text-center text-sm font-bold text-white shadow-lg shadow-amber-700/25 transition hover:bg-amber-800">
                        Mulai Sekarang
                    </a>
                    <a href="{{ route('landing.pricing') }}" class="min-h-11 rounded-xl border border-amber-700/25 bg-white/80 px-6 py-3 text-center text-sm font-bold text-[var(--brand-deep)] transition hover:bg-white">
                        Lihat Paket Harga
                    </a>
                </div>
                <div class="mt-8 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-xl border border-amber-700/15 bg-white/75 p-4">
                        <p class="text-2xl font-extrabold text-[var(--brand-deep)]">1x</p>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">Semua Proses Terpusat</p>
                    </div>
                    <div class="rounded-xl border border-amber-700/15 bg-white/75 p-4">
                        <p class="text-2xl font-extrabold text-[var(--brand-deep)]">24/7</p>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">Pengingat Jadwal Otomatis</p>
                    </div>
                    <div class="rounded-xl border border-amber-700/15 bg-white/75 p-4">
                        <p class="text-2xl font-extrabold text-[var(--brand-deep)]">+Rapi</p>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">Laporan Mudah Dibaca</p>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-amber-800/15 bg-white/80 p-4 shadow-2xl shadow-amber-900/10 backdrop-blur sm:p-5 md:p-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[var(--brand)] sm:text-sm sm:tracking-[0.2em]">Preview Experience</p>
                <div class="mt-4 space-y-4">
                    <div class="rounded-2xl border border-amber-700/15 bg-gradient-to-r from-amber-50 to-orange-50 p-4">
                        <p class="text-sm font-semibold text-[var(--brand-deep)]">Kalender Terhubung</p>
                        <p class="mt-1 text-sm text-[var(--muted)]">Setiap booking otomatis masuk ke kalender agar jadwal selalu sinkron dan minim bentrok.</p>
                    </div>
                    <div class="rounded-2xl border border-amber-700/15 bg-white p-4">
                        <p class="text-sm font-semibold text-[var(--brand-deep)]">Reminder Cerdas</p>
                        <p class="mt-1 text-sm text-[var(--muted)]">Notifikasi pengingat dikirim sebelum jadwal berlangsung untuk mengurangi no-show klien.</p>
                    </div>
                    <div class="rounded-2xl border border-amber-700/15 bg-white p-4">
                        <p class="text-sm font-semibold text-[var(--brand-deep)]">Laporan & Insight</p>
                        <p class="mt-1 text-sm text-[var(--muted)]">Pantau pendapatan, layanan terlaris, dan performa booking dalam tampilan yang cepat dipahami.</p>
                    </div>
                    <div class="rounded-2xl border border-amber-700/15 bg-white p-4">
                        <p class="text-sm font-semibold text-[var(--brand-deep)]">Kelola Layanan dengan Nilai Nyata</p>
                        <p class="mt-1 text-sm text-[var(--muted)]">Atur harga, durasi, dan paket layanan untuk tingkatkan nilai bisnis tanpa ribet operasional.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-10 rounded-3xl border border-amber-700/15 bg-white/70 p-4 shadow-lg backdrop-blur sm:mt-12 sm:p-6 md:mt-16 md:p-8">
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                <article class="rounded-2xl border border-amber-700/10 bg-white p-4">
                    <p class="text-sm font-bold text-[var(--brand-deep)]">Mudah Dipakai Sehari-hari</p>
                    <p class="mt-2 text-sm leading-6 text-[var(--muted)]">Tampilan bersih dan alur cepat, cocok untuk tim kecil sampai studio yang sedang tumbuh.</p>
                </article>
                <article class="rounded-2xl border border-amber-700/10 bg-white p-4">
                    <p class="text-sm font-bold text-[var(--brand-deep)]">Terhubung ke Kalender</p>
                    <p class="mt-2 text-sm leading-6 text-[var(--muted)]">Booking langsung tercatat agar kamu bisa fokus ke kualitas layanan, bukan urus tabrakan jadwal.</p>
                </article>
                <article class="rounded-2xl border border-amber-700/10 bg-white p-4">
                    <p class="text-sm font-bold text-[var(--brand-deep)]">Notifikasi Pengingat</p>
                    <p class="mt-2 text-sm leading-6 text-[var(--muted)]">Pengingat otomatis bantu kurangi klien lupa jadwal dan menjaga ritme operasional tetap stabil.</p>
                </article>
                <article class="rounded-2xl border border-amber-700/10 bg-white p-4">
                    <p class="text-sm font-bold text-[var(--brand-deep)]">Worth untuk Usaha</p>
                    <p class="mt-2 text-sm leading-6 text-[var(--muted)]">Gabungan fitur booking, laporan, dan manajemen layanan memberi dampak nyata untuk keuntungan bisnis.</p>
                </article>
            </div>
        </section>
    </main>
</body>
</html>

