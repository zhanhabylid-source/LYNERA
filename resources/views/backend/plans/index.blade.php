<x-backend-layout>
    <section class="space-y-5">
        <div>
            <h2 class="text-2xl font-semibold text-stone-900">Kelola Paket Harga</h2>
            <p class="mt-1 text-sm text-stone-600">Perubahan di sini akan meng-override `config/plans.php` pada runtime aplikasi.</p>
        </div>

        <div class="grid gap-4">
            @foreach ($plans as $plan)
                @php
                    $key = $plan['key'];
                    $effective = $plan['effective'];
                    $override = $plan['override'];
                    $flagKeys = array_keys((array) ($effective['feature_flags'] ?? []));
                    $featuresText = is_array($effective['features'] ?? null) ? implode("\n", $effective['features']) : '';
                    $promoStartsValue = ($effective['promo_starts_at'] ?? null) instanceof \DateTimeInterface
                        ? $effective['promo_starts_at']->format('Y-m-d\TH:i')
                        : '';
                    $promoEndsValue = ($effective['promo_ends_at'] ?? null) instanceof \DateTimeInterface
                        ? $effective['promo_ends_at']->format('Y-m-d\TH:i')
                        : '';
                    $promoStatus = $effective['promo_status'] ?? 'inactive';
                @endphp
                <article class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                        <h3 class="text-lg font-semibold text-stone-900">{{ strtoupper($key) }}</h3>
                        <div class="inline-flex items-center gap-2">
                            @if ($override)
                                <span class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Override Aktif</span>
                            @else
                                <span class="rounded-full border border-stone-200 bg-stone-50 px-2.5 py-1 text-xs font-semibold text-stone-600">Default Config</span>
                            @endif
                            @if($promoStatus === 'active')
                                <span class="rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700" data-testid="plan-promo-active-{{ $key }}">Promo Tayang</span>
                            @elseif($promoStatus === 'scheduled')
                                <span class="rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">Promo Terjadwal</span>
                            @elseif($promoStatus === 'expired')
                                <span class="rounded-full border border-stone-200 bg-stone-100 px-2.5 py-1 text-xs font-semibold text-stone-600">Promo Berakhir</span>
                            @endif
                        </div>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        <form method="POST" action="{{ route('backend.plans.update', $key) }}" class="contents">
                        @csrf
                        @method('PUT')
                        <label class="text-xs font-medium text-stone-600">
                            Nama Paket
                            <input type="text" name="name" value="{{ old('name', $effective['name'] ?? '') }}" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                        </label>
                        <label class="text-xs font-medium text-stone-600">
                            Harga
                            <input type="text" name="price" value="{{ old('price', $effective['price'] ?? '') }}" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm" data-testid="plan-regular-price-{{ $key }}">
                        </label>
                        <label class="text-xs font-medium text-stone-600">
                            Siklus Billing
                            <input type="text" name="billing_cycle" value="{{ old('billing_cycle', $effective['billing_cycle'] ?? '') }}" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                        </label>
                        <label class="text-xs font-medium text-stone-600">
                            Batas Booking Total (kosong = tanpa batas)
                            <input type="number" min="0" name="booking_limit_total" value="{{ old('booking_limit_total', $effective['booking_limit_total']) }}" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                        </label>
                        <label class="text-xs font-medium text-stone-600 md:col-span-2">
                            Benefit Ringkas
                            <textarea name="benefit" rows="2" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">{{ old('benefit', $effective['benefit'] ?? '') }}</textarea>
                        </label>
                        <div class="rounded-2xl border border-rose-200 bg-gradient-to-r from-rose-50 to-amber-50 p-4 md:col-span-2" data-testid="plan-promo-settings-{{ $key }}">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <p class="text-sm font-semibold text-stone-900">Promo Paket</p>
                                    <p class="mt-1 text-xs text-stone-600">Harga promo otomatis tampil di halaman Harga, Tagihan, dan form Upgrade selama periode aktif.</p>
                                </div>
                                <label class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-700">
                                    <input type="checkbox" name="promo_is_active" value="1" @checked((bool) old('promo_is_active', $effective['promo_is_active'] ?? false)) class="rounded border-stone-300 text-rose-600 focus:ring-rose-500" data-testid="plan-promo-toggle-{{ $key }}">
                                    Aktifkan Promo
                                </label>
                            </div>
                            <div class="mt-4 grid gap-3 md:grid-cols-2">
                                <label class="text-xs font-medium text-stone-600">
                                    Harga Promo
                                    <input type="text" name="promo_price" value="{{ old('promo_price', $effective['promo_price'] ?? '') }}" placeholder="Contoh: Rp 99.000" class="mt-1 w-full rounded-xl border border-stone-300 bg-white px-3 py-2 text-sm" data-testid="plan-promo-price-{{ $key }}">
                                </label>
                                <label class="text-xs font-medium text-stone-600">
                                    Label Promo
                                    <input type="text" name="promo_label" value="{{ old('promo_label', $effective['promo_label'] ?? '') }}" placeholder="Contoh: Promo Launching" class="mt-1 w-full rounded-xl border border-stone-300 bg-white px-3 py-2 text-sm" data-testid="plan-promo-label-{{ $key }}">
                                </label>
                                <label class="text-xs font-medium text-stone-600">
                                    Mulai Promo
                                    <input type="datetime-local" name="promo_starts_at" value="{{ old('promo_starts_at', $promoStartsValue) }}" class="mt-1 w-full rounded-xl border border-stone-300 bg-white px-3 py-2 text-sm" data-testid="plan-promo-start-{{ $key }}">
                                </label>
                                <label class="text-xs font-medium text-stone-600">
                                    Berakhir Promo
                                    <input type="datetime-local" name="promo_ends_at" value="{{ old('promo_ends_at', $promoEndsValue) }}" class="mt-1 w-full rounded-xl border border-stone-300 bg-white px-3 py-2 text-sm" data-testid="plan-promo-end-{{ $key }}">
                                </label>
                            </div>
                            @if($effective['promo_active'] ?? false)
                                <div class="mt-4 rounded-xl border border-rose-200 bg-white p-3 text-sm" data-testid="plan-promo-preview-{{ $key }}">
                                    <span class="font-semibold text-rose-700">{{ $effective['promo_label'] }}</span>
                                    <span class="ml-2 text-stone-400 line-through">{{ $effective['regular_price'] }}</span>
                                    <span class="ml-2 font-bold text-stone-900">{{ $effective['effective_price'] }}</span>
                                    @if($effective['promo_ends_label'])<span class="ml-2 text-xs text-stone-500">sampai {{ $effective['promo_ends_label'] }}</span>@endif
                                </div>
                            @endif
                        </div>
                        <label class="text-xs font-medium text-stone-600 md:col-span-2">
                            Fitur (1 baris = 1 fitur)
                            <textarea name="features_text" rows="4" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">{{ old('features_text', $featuresText) }}</textarea>
                        </label>
                        <div class="md:col-span-2">
                            <p class="text-xs font-medium text-stone-600">Feature Flags</p>
                            <div class="mt-2 flex flex-wrap gap-3">
                                @foreach ($flagKeys as $flagKey)
                                    <label class="inline-flex items-center gap-2 rounded-lg border border-stone-200 bg-stone-50 px-3 py-2 text-xs font-medium text-stone-700">
                                        <input
                                            type="checkbox"
                                            name="feature_flags[{{ $flagKey }}]"
                                            value="1"
                                            @checked((bool) old("feature_flags.$flagKey", $effective['feature_flags'][$flagKey] ?? false))
                                            class="rounded border-stone-300 text-stone-900 focus:ring-stone-500"
                                        >
                                        {{ $flagKey }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="md:col-span-2 flex flex-wrap gap-2 pt-1">
                            <button type="submit" class="rounded-xl bg-stone-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-black" data-testid="plan-save-{{ $key }}">
                                Simpan Override
                            </button>
                        </form>
                            <form method="POST" action="{{ route('backend.plans.reset', $key) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm font-semibold text-stone-700 hover:bg-stone-50">
                                    Reset ke Default
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
</x-backend-layout>
