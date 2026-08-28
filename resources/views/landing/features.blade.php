<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitur - {{ config('app.name', 'LYNERA') }}</title>
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
    </style>
</head>
<body class="font-body bg-gradient-to-br from-amber-50 via-orange-50 to-stone-100 text-[var(--ink)]">
    <header class="relative max-w-7xl mx-auto px-6 py-6">
        <div class="rounded-2xl border backdrop-blur border-[var(--line)] bg-[var(--surface)] px-4 py-3 md:px-6 md:py-4 flex items-center justify-between shadow-sm">
            <a href="{{ route('landing.home') }}" class="font-extrabold tracking-tight text-xl text-[var(--brand-deep)]">{{ config('app.name', 'LYNERA') }}</a>
            <nav class="flex items-center gap-4 text-sm font-semibold">
                <a href="{{ route('landing.home') }}" class="text-[var(--muted)] hover:text-[var(--brand-deep)]">Beranda</a>
                <a href="{{ route('landing.pricing') }}" class="text-[var(--muted)] hover:text-[var(--brand-deep)]">Harga</a>
                <a href="{{ route('register') }}" class="rounded-xl border border-amber-700/20 bg-amber-700 px-4 py-2 text-white shadow-sm hover:bg-amber-800">Coba Gratis</a>
            </nav>
        </div>
    </header>

    <main class="relative max-w-7xl mx-auto px-6 pb-20 md:pb-24">
        <section class="pt-8 text-center md:pt-12">
            <p class="inline-flex items-center rounded-full border border-amber-700/20 bg-white/80 px-4 py-1.5 text-xs font-bold uppercase tracking-[0.22em] text-[var(--brand)]">
                Fitur Untuk Operasional MUA
            </p>
            <h1 class="font-display mt-6 text-4xl leading-tight text-[var(--brand-deep)] md:text-5xl">
                {{ config('app.tagline', 'Smart Tools for Modern Makeup Artists') }}
            </h1>
            <p class="mx-auto mt-4 max-w-3xl text-base leading-7 text-[var(--muted)] md:text-lg">
                Kelola layanan, jadwal, pelanggan, dan pembayaran dalam satu workflow yang mudah dipakai setiap hari.
            </p>
        </section>

        <section class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            <article class="rounded-2xl border border-amber-700/15 bg-white/80 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-[var(--brand-deep)]">Kalender + Booking Bebas Bentrok</h2>
                <p class="mt-2 text-sm leading-6 text-[var(--muted)]">Setiap jadwal tercatat rapi, dengan validasi bentrok otomatis agar operasional lebih tenang.</p>
            </article>
            <article class="rounded-2xl border border-amber-700/15 bg-white/80 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-[var(--brand-deep)]">Reminder Booking Besok</h2>
                <p class="mt-2 text-sm leading-6 text-[var(--muted)]">Notifikasi H-1 membantu mengingatkan jadwal klien dan menekan risiko no-show.</p>
            </article>
            <article class="rounded-2xl border border-amber-700/15 bg-white/80 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-[var(--brand-deep)]">Pembayaran Bertahap + Invoice</h2>
                <p class="mt-2 text-sm leading-6 text-[var(--muted)]">Dukung alur DP dan pelunasan, plus catatan pembayaran yang transparan dan mudah dilacak.</p>
            </article>
            <article class="rounded-2xl border border-amber-700/15 bg-white/80 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-[var(--brand-deep)]">Laporan Kinerja Usaha</h2>
                <p class="mt-2 text-sm leading-6 text-[var(--muted)]">Pantau pendapatan dan performa layanan dalam tampilan ringkas yang cepat dipahami.</p>
            </article>
            <article class="rounded-2xl border border-amber-700/15 bg-white/80 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-[var(--brand-deep)]">Public Booking Form</h2>
                <p class="mt-2 text-sm leading-6 text-[var(--muted)]">Bagikan link booking publik agar calon klien bisa request jadwal dengan lebih praktis.</p>
            </article>
            <article class="rounded-2xl border border-amber-700/15 bg-white/80 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-[var(--brand-deep)]">Aman untuk Multi-Tenant</h2>
                <p class="mt-2 text-sm leading-6 text-[var(--muted)]">Data tiap bisnis tetap terisolasi sehingga aman dipakai banyak user secara bersamaan.</p>
            </article>
        </section>

        <section class="mt-10 rounded-3xl border border-amber-700/15 bg-white/75 p-6 text-center shadow-sm md:p-8">
            <p class="text-sm text-[var(--muted)]">Fitur dirancang untuk bikin operasional harian lebih ringan dan keputusan bisnis lebih cepat.</p>
            <div class="mt-4 flex flex-wrap justify-center gap-3">
                <a href="{{ route('register') }}" class="rounded-xl bg-amber-700 px-6 py-3 text-sm font-bold text-white hover:bg-amber-800">Mulai Sekarang</a>
                <a href="{{ route('landing.pricing') }}" class="rounded-xl border border-amber-700/25 bg-white px-6 py-3 text-sm font-bold text-[var(--brand-deep)] hover:bg-amber-50">Lihat Harga Paket</a>
            </div>
        </section>
    </main>
</body>
</html>


