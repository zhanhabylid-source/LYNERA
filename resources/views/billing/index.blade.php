<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-stone-800 leading-tight">Tagihan & Paket</h2>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-rose-50 via-amber-50 to-white min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="p-4 rounded-xl border border-red-200 bg-red-50 text-red-700">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="p-4 rounded-xl border border-red-200 bg-red-50 text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-rose-100 shadow-md p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-lg font-semibold text-stone-900">Paket Saat Ini</h3>
                    @if($plan !== 'free')
                        <button
                            type="button"
                            data-open-upgrade-request-modal
                            data-plan-key="{{ $plan }}"
                            data-plan-name="Perpanjang {{ $currentPlan['name'] }}"
                            data-plan-price="{{ $currentPlan['effective_price'] ?? $currentPlan['price'] }}"
                            class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700 disabled:opacity-60 disabled:cursor-not-allowed"
                            data-testid="billing-renew-button"
                            {{ $hasPendingUpgradeRequest ? 'disabled' : '' }}
                        >
                            Perpanjang 1 Bulan
                        </button>
                    @endif
                </div>
                <div class="mt-4 grid md:grid-cols-3 gap-4">
                    <div class="p-4 rounded-xl border border-stone-200 bg-stone-50">
                        <p class="text-sm text-stone-500">Nama Paket</p>
                        <p class="text-2xl font-bold text-stone-900 uppercase">{{ $plan }}</p>
                    </div>
                    <div class="p-4 rounded-xl border border-stone-200 bg-stone-50">
                        <p class="text-sm text-stone-500">Harga</p>
                        <p class="text-2xl font-bold text-stone-900">{{ $currentPlan['price'] }}</p>
                    </div>
                    <div class="p-4 rounded-xl border border-stone-200 bg-stone-50">
                        <p class="text-sm text-stone-500">Masa Aktif</p>
                        @if($expiresAt)
                            <p class="text-2xl font-bold {{ $expiresAt->isPast() ? 'text-rose-700' : 'text-stone-900' }}" data-testid="billing-masa-aktif">{{ $expiresAt->translatedFormat('d M Y') }}</p>
                            <p class="mt-1 text-xs {{ $expiresAt->isPast() ? 'text-rose-600' : 'text-stone-500' }}">{{ $expiresAt->isPast() ? 'Masa aktif sudah berakhir' : 'Sisa '.$trialDaysLeft.' hari lagi' }}</p>
                        @else
                            <p class="text-2xl font-bold text-stone-900" data-testid="billing-masa-aktif">Tanpa batas waktu</p>
                        @endif
                    </div>
                </div>
                @if($plan !== 'free' && $hasPendingUpgradeRequest)
                    <p class="mt-3 text-xs text-amber-700">Ada request aktif — selesaikan/menunggu validasi terlebih dahulu sebelum memperpanjang.</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-rose-100 shadow-md p-6">
                <h3 class="text-lg font-semibold text-stone-900">Fitur</h3>
                <div class="mt-4 grid md:grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl border border-stone-200 bg-stone-50">
                        <p class="text-sm text-stone-500">Batas Booking</p>
                        <p class="text-lg font-semibold text-stone-900">{{ $currentPlan['booking_limit_label'] }}</p>
                        <p class="mt-2 text-sm text-stone-600">
                            Total booking terpakai: {{ $bookingUsage['bookings_count'] }}
                            @if($bookingUsage['is_unlimited'])
                                booking
                            @else
                                dari {{ $bookingUsage['limit'] }} booking
                            @endif
                        </p>
                        @if(! $bookingUsage['is_unlimited'])
                            <div class="mt-2 h-2 rounded-full bg-stone-200">
                                <div class="h-2 rounded-full bg-rose-500" style="width: {{ $bookingUsage['percent_used'] }}%;"></div>
                            </div>
                            <p class="mt-2 text-xs text-stone-500">Sisa kuota total: {{ $bookingUsage['remaining'] }}</p>
                        @endif
                    </div>
                    <div class="p-4 rounded-xl border border-stone-200 bg-stone-50">
                        <p class="text-sm text-stone-500">Fitur Termasuk</p>
                        <ul class="mt-2 text-sm text-stone-700 space-y-1">
                            @foreach($currentPlan['features'] as $feature)
                                <li>- {{ $feature }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-rose-100 shadow-md p-6">
                <h3 class="text-lg font-semibold text-stone-900">Pilih Layanan Upgrade</h3>
                @if(count($availableUpgradePlans) === 0)
                    <div class="mt-3 rounded-xl border border-stone-200 bg-stone-50 p-4 text-sm text-stone-700">
                        Paket Anda saat ini adalah level tertinggi. Tidak ada opsi upgrade lanjutan.
                    </div>
                @else
                    <div class="mt-4 grid md:grid-cols-3 gap-4">
                        @foreach($availableUpgradePlans as $key => $planData)
                            @php
                                $isRekomendasi = $plan === 'free' && $key === 'pro';
                                $theme = match($planData['theme']) {
                                    'amber' => 'border-amber-300 bg-amber-50',
                                    'rose' => 'border-rose-300 bg-rose-50',
                                    default => 'border-stone-300 bg-stone-50'
                                };
                            @endphp
                            <div class="relative p-5 rounded-xl border {{ $theme }}">
                                @if($isRekomendasi)
                                    <span class="absolute -top-2 right-3 px-2 py-1 rounded bg-rose-600 text-white text-xs">Rekomendasi</span>
                                @endif
                                @if($planData['promo_active'] ?? false)
                                    <span class="absolute -top-2 left-3 rounded bg-rose-600 px-2 py-1 text-xs font-semibold text-white" data-testid="billing-promo-badge-{{ $key }}">{{ $planData['promo_label'] }}</span>
                                @endif
                                <h4 class="text-xl font-bold text-stone-900">{{ $planData['name'] }}</h4>
                                @if($planData['promo_active'] ?? false)
                                    <p class="mt-1 text-xs text-stone-400 line-through">{{ $planData['regular_price'] }}</p>
                                    <p class="text-xl font-bold text-rose-700" data-testid="billing-promo-price-{{ $key }}">{{ $planData['effective_price'] }}</p>
                                    @if($planData['promo_ends_label'])<p class="mt-1 text-xs text-rose-600">Sampai {{ $planData['promo_ends_label'] }}</p>@endif
                                @else
                                    <p class="mt-1 text-stone-700">{{ $planData['price'] }}</p>
                                @endif
                                <p class="mt-2 text-sm text-stone-600">{{ $planData['booking_limit_label'] }}</p>
                                <ul class="mt-3 text-sm text-stone-700 space-y-1">
                                    @foreach(($planData['features'] ?? []) as $feature)
                                        <li>- {{ $feature }}</li>
                                    @endforeach
                                </ul>
                                <button
                                    type="button"
                                    data-open-upgrade-request-modal
                                    data-plan-key="{{ $key }}"
                                    data-plan-name="{{ $planData['name'] }}"
                                    data-plan-price="{{ $planData['effective_price'] ?? $planData['price'] }}"
                                    class="inline-flex mt-4 px-4 py-2 rounded-xl bg-stone-800 text-white hover:bg-black transition disabled:opacity-60 disabled:cursor-not-allowed"
                                    {{ $hasPendingUpgradeRequest ? 'disabled' : '' }}
                                >
                                    Pilih Layanan
                                </button>
                            </div>
                        @endforeach
                    </div>
                    @if($hasPendingUpgradeRequest)
                        <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
                            Anda sudah memiliki request aktif. Selesaikan/menunggu validasi request saat ini terlebih dahulu.
                        </div>
                    @endif
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-rose-100 shadow-md p-6">
                <h3 class="text-lg font-semibold text-stone-900">Request Menunggu Pembayaran</h3>
                <p class="mt-1 text-xs text-stone-600">Request yang belum diverifikasi dapat kedaluwarsa dalam 1x24 jam.</p>
                <div class="mt-4 space-y-3">
                    @forelse($pendingPaymentRequests as $requestItem)
                        <details class="rounded-xl border border-stone-200 bg-stone-50 p-4">
                            <summary class="cursor-pointer text-sm font-semibold text-stone-900">
                                {{ strtoupper($requestItem->current_plan) }} -> {{ strtoupper($requestItem->requested_plan) }}
                                ({{ $requestItem->requested_price ?? '-' }}) - Klik untuk konfirmasi pembayaran
                            </summary>
                            <div class="mt-3 grid gap-4 md:grid-cols-2">
                                <div class="rounded-xl border border-stone-200 bg-white p-4" data-testid="upgrade-payment-methods">
                                    <p class="text-sm font-semibold text-stone-900">Informasi Pembayaran Upgrade</p>
                                    <div class="mt-3 space-y-3">
                                        @forelse($paymentMethods as $method)
                                            <div class="rounded-xl border {{ $method->is_primary ? 'border-rose-200 bg-rose-50' : 'border-stone-200 bg-stone-50' }} p-3" data-testid="upgrade-payment-method-{{ $method->id }}">
                                                <div class="flex items-center justify-between gap-2">
                                                    <p class="text-sm font-semibold text-stone-900">{{ $method->provider_name }}</p>
                                                    <span class="rounded-full bg-white px-2 py-1 text-[10px] font-semibold text-stone-600">{{ $method->typeLabel() }}</span>
                                                </div>
                                                @if($method->account_name)<p class="mt-2 text-xs text-stone-600">Atas nama: {{ $method->account_name }}</p>@endif
                                                @if($method->account_number)<p class="text-sm font-semibold text-stone-900">{{ $method->account_number }}</p>@endif
                                                @if($method->qrCodeUrl())<img src="{{ $method->qrCodeUrl() }}" alt="QRIS {{ $method->provider_name }}" class="mt-3 h-40 w-40 rounded-xl border border-stone-200 bg-white object-contain p-2">@endif
                                                @if($method->contact)<p class="mt-2 text-xs text-stone-600">Konfirmasi: {{ $method->contact }}</p>@endif
                                                @if($method->instructions)<p class="mt-2 text-xs leading-relaxed text-stone-600">{{ $method->instructions }}</p>@endif
                                            </div>
                                        @empty
                                            <p class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-700" data-testid="upgrade-payment-method-empty">Metode pembayaran belum tersedia. Hubungi admin LYNERA sebelum mengonfirmasi pembayaran.</p>
                                        @endforelse
                                    </div>
                                    <p class="mt-2 text-xs text-stone-600">Request dibuat: {{ $requestItem->created_at?->format('d M Y H:i') }}</p>
                                </div>
                                <form method="POST" action="{{ route('billing.upgrade-request.confirm-payment', $requestItem) }}" enctype="multipart/form-data" class="rounded-xl border border-stone-200 bg-white p-4 space-y-3">
                                    @csrf
                                    @method('PATCH')
                                    <label class="block text-sm font-medium text-stone-700">
                                        Metode Pembayaran
                                        @if($paymentMethods->isNotEmpty())
                                            <select name="payment_method" required class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm" data-testid="upgrade-payment-method-select-{{ $requestItem->id }}">
                                                <option value="">Pilih metode</option>
                                                @foreach($paymentMethods as $method)
                                                    <option value="{{ $method->displayLabel() }}">{{ $method->displayLabel() }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="text" disabled value="Belum tersedia" class="mt-1 w-full rounded-xl border border-stone-300 bg-stone-100 px-3 py-2 text-sm text-stone-500">
                                        @endif
                                    </label>
                                    <label class="block text-sm font-medium text-stone-700">
                                        Nama Pengirim
                                        <input type="text" name="payer_name" required class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                                    </label>
                                    <label class="block text-sm font-medium text-stone-700">
                                        Nomor Rekening/E-Wallet (opsional)
                                        <input type="text" name="payer_account_number" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                                    </label>
                                    <label class="block text-sm font-medium text-stone-700">
                                        Catatan Pembayaran (opsional)
                                        <textarea name="payment_note" rows="2" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm"></textarea>
                                    </label>
                                    <label class="block text-sm font-medium text-stone-700">
                                        Upload Bukti Pembayaran (JPG/PNG/PDF)
                                        <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" required class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                                    </label>
                                    <button type="submit" class="w-full rounded-xl bg-stone-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-black transition min-h-[44px] disabled:cursor-not-allowed disabled:opacity-50" data-testid="upgrade-payment-submit-{{ $requestItem->id }}" @disabled($paymentMethods->isEmpty())>
                                        Konfirmasi Pembayaran
                                    </button>
                                </form>
                            </div>
                        </details>
                    @empty
                        <div class="rounded-xl border border-dashed border-stone-300 bg-stone-50 p-4 text-sm text-stone-600">
                            Tidak ada request yang menunggu pembayaran.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-rose-100 shadow-md p-6">
                <h3 class="text-lg font-semibold text-stone-900">Histori Request Langganan</h3>
                <div class="mt-4 space-y-3">
                    @forelse($upgradeRequests as $requestItem)
                        @php
                            $statusLabel = match ($requestItem->status) {
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'expired' => 'Kedaluwarsa',
                                'pending_payment' => 'Menunggu Pembayaran',
                                default => 'Menunggu Validasi',
                            };
                            $statusClass = match ($requestItem->status) {
                                'approved' => 'bg-emerald-100 text-emerald-700',
                                'rejected' => 'bg-red-100 text-red-700',
                                'expired' => 'bg-stone-200 text-stone-700',
                                'pending_payment' => 'bg-blue-100 text-blue-700',
                                default => 'bg-amber-100 text-amber-700',
                            };
                            $normalizedProofPath = str_replace('\\', '/', (string) ($requestItem->proof_path ?? ''));
                            $proofUrl = ($normalizedProofPath !== '' && $normalizedProofPath !== '-')
                                ? \Illuminate\Support\Facades\Storage::disk('public')->url($normalizedProofPath)
                                : '';
                            $proofExt = strtolower(pathinfo($normalizedProofPath, PATHINFO_EXTENSION));
                            $proofIsImage = in_array($proofExt, ['jpg', 'jpeg', 'png', 'webp'], true);
                        @endphp
                        <div class="rounded-xl border border-stone-200 bg-stone-50 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-stone-900">
                                    {{ strtoupper($requestItem->current_plan) }} -> {{ strtoupper($requestItem->requested_plan) }}
                                    ({{ $requestItem->requested_price ?? '-' }})
                                </p>
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                            <p class="mt-2 text-xs text-stone-600">
                                Diajukan: {{ $requestItem->created_at?->format('d M Y H:i') }} |
                                Metode: {{ $requestItem->payment_method }} |
                                Pengirim: {{ $requestItem->payer_name }}
                            </p>
                            <button
                                type="button"
                                class="inline-flex mt-2 text-sm text-rose-700 hover:text-rose-800"
                                data-request-detail
                                data-request-id="{{ $requestItem->id }}"
                                data-current-plan="{{ strtoupper($requestItem->current_plan) }}"
                                data-requested-plan="{{ strtoupper($requestItem->requested_plan) }}"
                                data-requested-price="{{ $requestItem->requested_price ?? '-' }}"
                                data-status-label="{{ $statusLabel }}"
                                data-status-class="{{ $statusClass }}"
                                data-created-at="{{ $requestItem->created_at?->format('d M Y H:i') ?? '-' }}"
                                data-payment-method="{{ $requestItem->payment_method }}"
                                data-payer-name="{{ $requestItem->payer_name }}"
                                data-payer-account="{{ $requestItem->payer_account_number ?? '-' }}"
                                data-payment-note="{{ $requestItem->payment_note ?? '-' }}"
                                data-review-note="{{ $requestItem->review_note ?? '-' }}"
                                data-reviewed-at="{{ $requestItem->reviewed_at?->format('d M Y H:i') ?? '-' }}"
                                data-proof-url="{{ $proofUrl }}"
                                data-proof-image="{{ $proofIsImage ? '1' : '0' }}"
                            >
                                Detail Request
                            </button>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-stone-300 bg-stone-50 p-4 text-sm text-stone-600">
                            Belum ada histori request langganan.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-rose-100 shadow-md p-6">
                <h3 class="text-lg font-semibold text-stone-900">Histori Langganan</h3>
                <div class="mt-4 space-y-3">
                    @forelse($subscriptionHistories as $history)
                        @php
                            $metadata = is_array($history->metadata) ? $history->metadata : [];
                            $historyPlan = strtoupper((string) ($metadata['plan'] ?? '-'));
                            $historyExpiredAt = $metadata['expired_at'] ?? null;
                        @endphp
                        <div class="rounded-xl border border-stone-200 bg-stone-50 p-4">
                            <p class="text-sm font-semibold text-stone-900">
                                Upgrade ke paket {{ $historyPlan }}
                            </p>
                            <p class="mt-1 text-xs text-stone-600">
                                Tanggal proses: {{ $history->created_at?->format('d M Y H:i') ?? '-' }}
                            </p>
                            <p class="mt-1 text-xs text-stone-600">
                                Masa aktif: {{ $historyExpiredAt ? \Carbon\Carbon::parse($historyExpiredAt)->format('d M Y H:i') : 'Tanpa batas waktu' }}
                            </p>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-stone-300 bg-stone-50 p-4 text-sm text-stone-600">
                            Belum ada histori langganan.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div id="upgrade-request-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40" data-upgrade-request-close></div>
        <div class="relative mx-auto mt-14 w-[94%] max-w-xl rounded-2xl border border-rose-100 bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-3">
                <h3 class="text-lg font-semibold text-stone-900">Form Request Upgrade Paket</h3>
                <button type="button" class="rounded-lg px-2 py-1 text-stone-500 hover:bg-stone-100" data-upgrade-request-close>x</button>
            </div>
            <form method="POST" action="{{ route('billing.upgrade-request') }}" class="mt-4 space-y-3">
                @csrf
                <input type="hidden" name="requested_plan" id="upgrade-request-plan-key" value="">
                <div class="rounded-xl border border-stone-200 bg-stone-50 p-3 text-sm">
                    <p class="text-stone-500">Paket Dipilih</p>
                    <p id="upgrade-request-plan-name" class="mt-1 font-semibold text-stone-900">-</p>
                    <p id="upgrade-request-plan-price" class="text-stone-700">-</p>
                </div>
                <label class="block text-sm font-medium text-stone-700">
                    Catatan Request (opsional)
                    <textarea name="request_note" rows="3" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm"></textarea>
                </label>
                <button type="submit" class="w-full rounded-xl bg-stone-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-black transition min-h-[44px]">
                    Kirim Request Upgrade
                </button>
            </form>
        </div>
    </div>

    <div id="upgrade-request-detail-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40" data-request-modal-close></div>
        <div class="relative mx-auto mt-10 w-[94%] max-w-2xl rounded-2xl border border-rose-100 bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-3">
                <h3 class="text-lg font-semibold text-stone-900">Detail Request Upgrade</h3>
                <button type="button" class="rounded-lg px-2 py-1 text-stone-500 hover:bg-stone-100" data-request-modal-close>x</button>
            </div>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <div class="rounded-xl bg-stone-50 p-3">
                    <p class="text-stone-500">Request ID</p>
                    <p id="request-modal-id" class="mt-1 font-medium text-stone-800">-</p>
                </div>
                <div class="rounded-xl bg-stone-50 p-3">
                    <p class="text-stone-500">Status</p>
                    <p id="request-modal-status" class="mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-medium">-</p>
                </div>
                <div class="rounded-xl bg-stone-50 p-3 md:col-span-2">
                    <p class="text-stone-500">Paket</p>
                    <p id="request-modal-plan" class="mt-1 font-medium text-stone-800">-</p>
                </div>
                <div class="rounded-xl bg-stone-50 p-3">
                    <p class="text-stone-500">Diajukan</p>
                    <p id="request-modal-created" class="mt-1 font-medium text-stone-800">-</p>
                </div>
                <div class="rounded-xl bg-stone-50 p-3">
                    <p class="text-stone-500">Metode Pembayaran</p>
                    <p id="request-modal-method" class="mt-1 font-medium text-stone-800">-</p>
                </div>
                <div class="rounded-xl bg-stone-50 p-3">
                    <p class="text-stone-500">Nama Pengirim</p>
                    <p id="request-modal-payer" class="mt-1 font-medium text-stone-800">-</p>
                </div>
                <div class="rounded-xl bg-stone-50 p-3">
                    <p class="text-stone-500">No Rekening / E-Wallet</p>
                    <p id="request-modal-account" class="mt-1 font-medium text-stone-800">-</p>
                </div>
                <div class="rounded-xl bg-stone-50 p-3 md:col-span-2">
                    <p class="text-stone-500">Catatan Pembayaran</p>
                    <p id="request-modal-note" class="mt-1 font-medium text-stone-800 whitespace-pre-line">-</p>
                </div>
                <div class="rounded-xl bg-stone-50 p-3 md:col-span-2">
                    <p class="text-stone-500">Catatan Status (Super Admin)</p>
                    <p id="request-modal-review-note" class="mt-1 font-medium text-stone-800 whitespace-pre-line">-</p>
                    <p id="request-modal-reviewed-at" class="mt-1 text-xs text-stone-500">-</p>
                </div>
            </div>
            <div class="mt-4 rounded-xl border border-stone-200 bg-stone-50 p-4">
                <p class="text-sm font-medium text-stone-800">Bukti Pembayaran</p>
                <img id="request-modal-proof-image" alt="Bukti pembayaran" class="mt-3 hidden max-h-80 rounded-xl border border-stone-200 object-contain" />
                <a id="request-modal-proof-link" href="#" target="_blank" rel="noopener noreferrer" class="mt-3 hidden inline-flex rounded-lg bg-stone-800 px-3 py-1.5 text-sm text-white hover:bg-black">
                    Buka File Bukti Pembayaran
                </a>
                <p id="request-modal-proof-empty" class="mt-2 text-xs text-stone-500">Bukti pembayaran tidak tersedia.</p>
            </div>
            <div class="mt-5 flex justify-end">
                <button type="button" class="rounded-xl bg-rose-500 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-600 min-h-[44px]" data-request-modal-close>
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const requestModal = document.getElementById('upgrade-request-modal');
            const planKeyInput = document.getElementById('upgrade-request-plan-key');
            const planName = document.getElementById('upgrade-request-plan-name');
            const planPrice = document.getElementById('upgrade-request-plan-price');
            const openRequestModal = () => requestModal?.classList.remove('hidden');
            const closeRequestModal = () => requestModal?.classList.add('hidden');

            requestModal?.querySelectorAll('[data-upgrade-request-close]').forEach((element) => {
                element.addEventListener('click', closeRequestModal);
            });

            document.querySelectorAll('[data-open-upgrade-request-modal]').forEach((button) => {
                button.addEventListener('click', () => {
                    const key = button.dataset.planKey || '';
                    const name = button.dataset.planName || '-';
                    const price = button.dataset.planPrice || '-';
                    if (planKeyInput) planKeyInput.value = key;
                    if (planName) planName.textContent = name;
                    if (planPrice) planPrice.textContent = price;
                    openRequestModal();
                });
            });

            const modal = document.getElementById('upgrade-request-detail-modal');
            if (!modal) {
                return;
            }

            const requestId = document.getElementById('request-modal-id');
            const requestStatus = document.getElementById('request-modal-status');
            const requestPlan = document.getElementById('request-modal-plan');
            const requestCreated = document.getElementById('request-modal-created');
            const requestMethod = document.getElementById('request-modal-method');
            const requestPayer = document.getElementById('request-modal-payer');
            const requestAccount = document.getElementById('request-modal-account');
            const requestNote = document.getElementById('request-modal-note');
            const requestReviewNote = document.getElementById('request-modal-review-note');
            const requestReviewedAt = document.getElementById('request-modal-reviewed-at');
            const proofImage = document.getElementById('request-modal-proof-image');
            const proofLink = document.getElementById('request-modal-proof-link');
            const proofEmpty = document.getElementById('request-modal-proof-empty');
            if (proofImage) {
                proofImage.addEventListener('error', () => {
                    proofImage.classList.add('hidden');
                    if (proofLink && proofLink.getAttribute('href') && proofLink.getAttribute('href') !== '#') {
                        proofLink.classList.remove('hidden');
                    }
                    if (proofEmpty) {
                        proofEmpty.classList.remove('hidden');
                        proofEmpty.textContent = 'Preview gambar gagal dimuat. Silakan buka file bukti pembayaran.';
                    }
                });
            }

            const openModal = () => modal.classList.remove('hidden');
            const closeModal = () => modal.classList.add('hidden');

            modal.querySelectorAll('[data-request-modal-close]').forEach((element) => {
                element.addEventListener('click', closeModal);
            });

            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeModal();
                    closeRequestModal();
                }
            });

            document.querySelectorAll('[data-request-detail]').forEach((button) => {
                button.addEventListener('click', () => {
                    const statusClass = button.dataset.statusClass || '';
                    requestId.textContent = button.dataset.requestId || '-';
                    requestStatus.textContent = button.dataset.statusLabel || '-';
                    requestStatus.className = `mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${statusClass}`;
                    requestPlan.textContent = `${button.dataset.currentPlan || '-'} -> ${button.dataset.requestedPlan || '-'} (${button.dataset.requestedPrice || '-'})`;
                    requestCreated.textContent = button.dataset.createdAt || '-';
                    requestMethod.textContent = button.dataset.paymentMethod || '-';
                    requestPayer.textContent = button.dataset.payerName || '-';
                    requestAccount.textContent = button.dataset.payerAccount || '-';
                    requestNote.textContent = button.dataset.paymentNote || '-';
                    requestReviewNote.textContent = button.dataset.reviewNote || '-';
                    requestReviewedAt.textContent = `Waktu review: ${button.dataset.reviewedAt || '-'}`;

                    const proofUrl = button.dataset.proofUrl || '';
                    const isImage = button.dataset.proofImage === '1';
                    proofImage.classList.add('hidden');
                    proofLink.classList.add('hidden');
                    proofEmpty.classList.remove('hidden');
                    proofImage.removeAttribute('src');
                    proofLink.setAttribute('href', '#');

                    if (proofUrl) {
                        proofLink.setAttribute('href', proofUrl);
                        proofLink.classList.remove('hidden');
                        if (isImage) {
                            proofImage.setAttribute('src', proofUrl);
                            proofImage.classList.remove('hidden');
                        }
                        proofEmpty.classList.add('hidden');
                    }

                    openModal();
                });
            });
        });
    </script>
</x-app-layout>
