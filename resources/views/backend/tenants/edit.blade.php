<x-backend-layout>
    <section class="space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-stone-900">Kelola Tenant: {{ $tenant->name }}</h2>
                <p class="mt-1 text-sm text-stone-600">{{ $tenant->email }}</p>
            </div>
            <a href="{{ route('backend.tenants.index') }}" class="rounded-xl border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50">
                Kembali ke List
            </a>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <article class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <h3 class="text-lg font-semibold text-stone-900">Informasi Dasar</h3>
                <form method="POST" action="{{ route('backend.tenants.update', $tenant) }}" class="mt-4 space-y-3">
                    @csrf
                    @method('PUT')
                    <label class="block text-sm font-medium text-stone-700">
                        Nama
                        <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block text-sm font-medium text-stone-700">
                        Email
                        <input type="email" name="email" value="{{ old('email', $tenant->email) }}" required class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block text-sm font-medium text-stone-700">
                        Role
                        <select name="role" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                            @foreach($roles as $role)
                                <option value="{{ $role }}" @selected(old('role', $tenant->role) === $role)>{{ strtoupper($role) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button type="submit" class="rounded-xl bg-stone-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-black">
                        Simpan Informasi Dasar
                    </button>
                </form>
            </article>

            <article class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <h3 class="text-lg font-semibold text-stone-900">Subscription</h3>
                <form method="POST" action="{{ route('backend.tenants.subscription.update', $tenant) }}" class="mt-4 space-y-3">
                    @csrf
                    @method('PATCH')
                    <label class="block text-sm font-medium text-stone-700">
                        Plan
                        <select name="plan" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                            @foreach($plans as $plan)
                                <option value="{{ $plan }}" @selected(old('plan', $subscription->plan) === $plan)>{{ strtoupper($plan) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm font-medium text-stone-700">
                        Expired At
                        <input type="date" name="expired_at" value="{{ old('expired_at', optional($subscription->expired_at)->toDateString()) }}" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block text-sm font-medium text-stone-700">
                        Bookings Consumed Total
                        <input type="number" min="0" name="bookings_consumed_total" value="{{ old('bookings_consumed_total', (int) $subscription->bookings_consumed_total) }}" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                    </label>
                    <button type="submit" class="rounded-xl bg-stone-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-black">
                        Simpan Subscription
                    </button>
                </form>
            </article>

            <article class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <h3 class="text-lg font-semibold text-stone-900">Suspend Tenant</h3>
                @if($tenant->is_suspended)
                    <p class="mt-2 text-sm text-rose-700">Tenant sedang disuspend sejak {{ optional($tenant->suspended_at)->format('d M Y H:i') }}.</p>
                @else
                    <p class="mt-2 text-sm text-stone-600">Tenant saat ini aktif.</p>
                @endif
                <form method="POST" action="{{ route('backend.tenants.suspend.update', $tenant) }}" class="mt-4 space-y-3">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="is_suspended" value="{{ $tenant->is_suspended ? '0' : '1' }}">
                    @if(! $tenant->is_suspended)
                        <label class="block text-sm font-medium text-stone-700">
                            Alasan Suspend
                            <input type="text" name="suspended_reason" value="{{ old('suspended_reason') }}" placeholder="Contoh: pelanggaran kebijakan" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                        </label>
                    @endif
                    <button type="submit" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-white {{ $tenant->is_suspended ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700' }}">
                        {{ $tenant->is_suspended ? 'Aktifkan Kembali Tenant' : 'Suspend Tenant' }}
                    </button>
                </form>
            </article>

            <article class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <h3 class="text-lg font-semibold text-stone-900">Reset Password</h3>
                <p class="mt-2 text-sm text-stone-600">Kosongkan password untuk generate password acak otomatis.</p>
                @if(session('tenant_new_password'))
                    <div
                        x-data="{ pw: @js(session('tenant_new_password')), copied: false, copy() { navigator.clipboard.writeText(this.pw).then(() => { this.copied = true; setTimeout(() => this.copied = false, 2000); }); } }"
                        class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3"
                        data-testid="tenant-new-password-panel"
                    >
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Password baru (simpan sekarang)</p>
                        <div class="mt-2 flex items-center gap-2">
                            <code class="flex-1 select-all rounded-lg bg-white px-3 py-2 font-mono text-sm text-stone-900" data-testid="tenant-new-password-value" x-text="pw"></code>
                            <button type="button" @click="copy()" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700" data-testid="tenant-new-password-copy">
                                <span x-show="!copied">Salin</span>
                                <span x-show="copied" x-cloak>Tersalin!</span>
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-emerald-700">Password ini hanya ditampilkan sekali. Berikan ke tenant lewat kanal aman.</p>
                    </div>
                @endif
                <form method="POST" action="{{ route('backend.tenants.password-reset', $tenant) }}" class="mt-4 space-y-3">
                    @csrf
                    @method('PATCH')
                    <label class="block text-sm font-medium text-stone-700">
                        Password Baru (opsional)
                        <input type="password" name="password" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block text-sm font-medium text-stone-700">
                        Konfirmasi Password Baru
                        <input type="password" name="password_confirmation" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                    </label>
                    <button type="submit" class="rounded-xl bg-stone-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-black">
                        Reset Password
                    </button>
                </form>
            </article>
        </div>

        <article class="rounded-2xl border border-rose-200 bg-rose-50/50 p-5 shadow-sm">
            <h3 class="text-lg font-semibold text-stone-900">Request Upgrade Tenant</h3>
            <div class="mt-3 space-y-3">
                @forelse($upgradeRequests as $requestItem)
                    @php
                        $statusLabel = match ($requestItem->status) {
                            'approved' => 'Disetujui',
                            'rejected' => 'Ditolak',
                            'pending_payment' => 'Menunggu Pembayaran',
                            'expired' => 'Kedaluwarsa',
                            default => 'Menunggu',
                        };
                        $statusClass = match ($requestItem->status) {
                            'approved' => 'bg-emerald-100 text-emerald-700',
                            'rejected' => 'bg-red-100 text-red-700',
                            'pending_payment' => 'bg-blue-100 text-blue-700',
                            'expired' => 'bg-stone-200 text-stone-700',
                            default => 'bg-amber-100 text-amber-700',
                        };
                    @endphp
                    <div class="rounded-xl border border-stone-200 bg-white p-4" x-data="{ proofOpen: false }" @keydown.escape.window="proofOpen = false">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-stone-900">
                                {{ strtoupper($requestItem->current_plan) }} -> {{ strtoupper($requestItem->requested_plan) }}
                                ({{ $requestItem->requested_price ?? '-' }})
                            </p>
                            <span class="rounded-full px-2 py-1 text-xs font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>
                        <p class="mt-1 text-xs text-stone-600">
                            Pengirim: {{ $requestItem->payer_name }} | Metode: {{ $requestItem->payment_method }} |
                            Diajukan: {{ $requestItem->created_at?->format('d M Y H:i') }}
                        </p>
                        @if($requestItem->proof_path)
                            @php
                                $proofUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($requestItem->proof_path);
                                $proofExtension = \Illuminate\Support\Str::lower(pathinfo($requestItem->proof_path, PATHINFO_EXTENSION));
                                $isImageProof = in_array($proofExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true);
                            @endphp
                            @if($isImageProof)
                                <button type="button" class="mt-3 block overflow-hidden rounded-xl border border-rose-100 bg-stone-50 text-left hover:border-rose-300" @click="proofOpen = true" data-testid="upgrade-proof-thumbnail-{{ $requestItem->id }}">
                                    <img src="{{ $proofUrl }}" alt="Bukti pembayaran upgrade {{ $requestItem->id }}" class="h-32 w-full max-w-xs object-cover" data-testid="upgrade-proof-image-{{ $requestItem->id }}">
                                    <span class="block px-3 py-2 text-xs font-semibold text-rose-700">Klik untuk memperbesar bukti pembayaran</span>
                                </button>
                                <div x-show="proofOpen" x-cloak x-transition class="fixed inset-0 z-[70] flex items-center justify-center bg-stone-950/80 p-4" role="dialog" aria-modal="true" aria-label="Perbesar bukti pembayaran" @click.self="proofOpen = false" data-testid="upgrade-proof-lightbox-{{ $requestItem->id }}">
                                    <div class="relative max-h-full max-w-4xl rounded-2xl bg-white p-2 shadow-2xl">
                                        <button type="button" class="absolute right-3 top-3 inline-flex h-9 w-9 items-center justify-center rounded-full bg-stone-900/80 text-xl text-white hover:bg-stone-900" @click="proofOpen = false" aria-label="Tutup bukti pembayaran" data-testid="upgrade-proof-lightbox-close-{{ $requestItem->id }}">&times;</button>
                                        <img src="{{ $proofUrl }}" alt="Bukti pembayaran upgrade ukuran penuh" class="max-h-[85vh] max-w-[90vw] rounded-xl object-contain" data-testid="upgrade-proof-lightbox-image-{{ $requestItem->id }}">
                                    </div>
                                </div>
                            @else
                                <a href="{{ $proofUrl }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex text-sm font-medium text-rose-700 hover:text-rose-800" data-testid="upgrade-proof-pdf-link-{{ $requestItem->id }}">
                                    Lihat Bukti Pembayaran ({{ strtoupper($proofExtension ?: 'FILE') }})
                                </a>
                            @endif
                        @endif
                        @if($requestItem->status === 'pending')
                            <div class="mt-3 grid gap-2 md:grid-cols-2">
                                <form method="POST" action="{{ route('backend.tenants.upgrade-requests.approve', [$tenant, $requestItem]) }}" class="space-y-2">
                                    @csrf
                                    @method('PATCH')
                                    <textarea name="review_note" rows="2" required placeholder="Catatan approval (wajib)" class="w-full rounded-xl border border-stone-300 px-3 py-2 text-xs"></textarea>
                                    <button type="submit" class="rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                                        Validasi & Setujui Request
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('backend.tenants.upgrade-requests.reject', [$tenant, $requestItem]) }}" class="space-y-2">
                                    @csrf
                                    @method('PATCH')
                                    <textarea name="review_note" rows="2" required placeholder="Alasan penolakan (wajib)" class="w-full rounded-xl border border-stone-300 px-3 py-2 text-xs"></textarea>
                                    <button type="submit" class="rounded-xl bg-rose-600 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-700">
                                        Tolak Request
                                    </button>
                                </form>
                            </div>
                        @else
                            <p class="mt-2 text-xs text-stone-600">
                                Diproses oleh: {{ $requestItem->reviewer?->name ?? '-' }} |
                                Waktu review: {{ $requestItem->reviewed_at?->format('d M Y H:i') ?? '-' }}
                            </p>
                            @if($requestItem->review_note)
                                <p class="mt-1 text-xs text-stone-600">Catatan: {{ $requestItem->review_note }}</p>
                            @endif
                        @endif
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-stone-300 bg-white p-4 text-sm text-stone-600">
                        Belum ada request upgrade dari tenant ini.
                    </div>
                @endforelse
            </div>
        </article>

        <article class="rounded-2xl border border-rose-200 bg-rose-50/50 p-5 shadow-sm">
            <h3 class="text-lg font-semibold text-rose-700">Zona Berbahaya</h3>
            <p class="mt-1 text-sm text-rose-700">Hapus tenant akan menghapus seluruh data tenant secara permanen.</p>
            <form method="POST" action="{{ route('backend.tenants.destroy', $tenant) }}" class="mt-4">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    onclick="return confirm('Yakin ingin menghapus tenant ini beserta seluruh datanya?')"
                    class="rounded-xl border border-rose-300 bg-white px-4 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-100"
                >
                    Hapus Tenant Permanen
                </button>
            </form>
        </article>
    </section>
</x-backend-layout>
