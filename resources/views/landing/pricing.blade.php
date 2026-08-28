<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Harga Paket - {{ config('app.name', 'LYNERA') }}</title>
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
                <a href="{{ route('landing.features') }}" class="text-[var(--muted)] hover:text-[var(--brand-deep)]">Fitur</a>
                <a href="{{ route('register') }}" class="rounded-xl border border-amber-700/20 bg-amber-700 px-4 py-2 text-white shadow-sm hover:bg-amber-800">Coba Gratis</a>
            </nav>
        </div>
    </header>

    <main class="relative max-w-7xl mx-auto px-6 pb-20 md:pb-24">
        <section class="pt-8 text-center md:pt-12">
            <p class="inline-flex items-center rounded-full border border-amber-700/20 bg-white/80 px-4 py-1.5 text-xs font-bold uppercase tracking-[0.22em] text-[var(--brand)]">
                Paket SaaS LYNERA
            </p>
            <h1 class="font-display mt-6 text-4xl leading-tight text-[var(--brand-deep)] md:text-5xl">
                Paket Harga Sederhana untuk Bisnis MUA
            </h1>
            <p class="mx-auto mt-4 max-w-3xl text-base leading-7 text-[var(--muted)] md:text-lg">
                Pilih paket sesuai tahap bisnismu. Terminologi paket, kuota, dan fitur mengikuti satu source of truth.
            </p>
        </section>

        <section class="mt-10 grid gap-6 md:grid-cols-3">
            @foreach($plans as $key => $plan)
                @php
                    $isRecommended = $defaultPlan === 'free' && $key === 'pro';
                    $themeClasses = match($plan['theme'] ?? 'stone') {
                        'amber' => 'border-amber-300',
                        'rose' => 'border-orange-300',
                        default => 'border-stone-200',
                    };
                @endphp
                <article class="relative rounded-2xl border {{ $themeClasses }} bg-white/90 p-6 shadow-sm {{ $isRecommended ? 'shadow-xl shadow-amber-900/10' : '' }}">
                    @if($isRecommended)
                        <span class="absolute -top-2 right-4 rounded-lg bg-amber-700 px-2 py-1 text-xs font-semibold text-white">Rekomendasi</span>
                    @endif
                    @if($plan['promo_active'] ?? false)
                        <span class="absolute -top-2 left-4 rounded-lg bg-rose-600 px-2.5 py-1 text-xs font-bold text-white shadow-sm" data-testid="pricing-promo-badge-{{ $key }}">{{ $plan['promo_label'] }}</span>
                    @endif
                    <h2 class="text-xl font-extrabold text-[var(--brand-deep)]">{{ $plan['name'] }}</h2>
                    <p class="mt-2 text-sm text-[var(--muted)]">{{ $plan['benefit'] }}</p>
                    @if($plan['promo_active'] ?? false)
                        <p class="mt-4 text-sm font-semibold text-stone-400 line-through" data-testid="pricing-regular-price-{{ $key }}">{{ $plan['regular_price'] }}</p>
                        <p class="text-3xl font-extrabold text-rose-700" data-testid="pricing-promo-price-{{ $key }}">{{ $plan['effective_price'] }}</p>
                        @if($plan['promo_ends_label'])
                            <p class="mt-1 text-xs font-semibold text-rose-600">Berlaku sampai {{ $plan['promo_ends_label'] }}</p>
                        @endif
                    @else
                        <p class="mt-4 text-3xl font-extrabold text-[var(--brand-deep)]">{{ $plan['price'] }}</p>
                    @endif
                    <p class="text-xs uppercase tracking-wider text-[var(--muted)]">{{ $plan['billing_cycle'] }}</p>
                    <p class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-900">{{ $plan['booking_limit_label'] }}</p>
                    <ul class="mt-4 space-y-2 text-sm text-[var(--muted)]">
                        @foreach($plan['features'] as $feature)
                            <li class="rounded-lg bg-stone-50 px-3 py-2">{{ $feature }}</li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register', ['plan' => $key]) }}" class="mt-5 inline-flex rounded-xl bg-amber-700 px-4 py-2 text-sm font-bold text-white hover:bg-amber-800">
                        {{ $plan['cta_label'] }}
                    </a>
                </article>
            @endforeach
        </section>

        <section class="mt-8 rounded-2xl border border-amber-700/15 bg-white/75 p-4 text-center text-sm text-[var(--muted)] md:p-5">
            Paket Free dapat digunakan selamanya dengan batas maksimal 10 booking total.
        </section>
    </main>
</body>
</html>

