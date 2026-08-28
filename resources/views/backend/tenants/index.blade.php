<x-backend-layout>
    <section class="space-y-5" x-data="tenantManager()">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-stone-900">Kelola Subscriber Tenant</h2>
                <p class="mt-1 text-sm text-stone-600">Pantau status, paket, masa berlaku, dan lakukan aksi cepat tanpa membuka detail.</p>
            </div>
            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                <form method="GET" action="{{ route('backend.tenants.index') }}" class="flex w-full gap-2 sm:w-auto">
                    <input
                        type="search"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Cari nama/email tenant"
                        class="w-full rounded-xl border border-stone-300 px-3 py-2 text-sm focus:border-stone-500 focus:outline-none focus:ring-0 sm:w-72"
                        data-testid="tenant-search-input"
                    >
                    @if ($planFilter !== '')
                        <input type="hidden" name="plan" value="{{ $planFilter }}">
                    @endif
                    @if ($statusFilter !== '')
                        <input type="hidden" name="status" value="{{ $statusFilter }}">
                    @endif
                    <button type="submit" class="rounded-xl bg-stone-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black" data-testid="tenant-search-button">Cari</button>
                </form>
                <a href="{{ route('backend.tenants.create') }}" class="rounded-xl border border-stone-300 bg-white px-4 py-2 text-center text-sm font-semibold text-stone-700 hover:bg-stone-50" data-testid="tenant-create-link">
                    + Tenant Baru
                </a>
            </div>
        </div>

        {{-- Ringkasan / stat cepat (klik untuk filter status) --}}
        @php
            $statusBaseQuery = array_filter(['q' => $search, 'plan' => $planFilter], fn ($v) => $v !== '' && $v !== null);
        @endphp
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4" data-testid="tenant-stats">
            <a href="{{ route('backend.tenants.index', $statusBaseQuery) }}"
               class="rounded-2xl border bg-white p-4 shadow-sm transition hover:shadow {{ $statusFilter === '' ? 'border-stone-900 ring-1 ring-stone-900' : 'border-stone-200 hover:border-stone-300' }}"
               data-testid="tenant-stat-total">
                <p class="text-xs font-medium uppercase tracking-wide text-stone-500">Total Tenant</p>
                <p class="mt-1 text-2xl font-bold text-stone-900">{{ number_format($stats['total']) }}</p>
            </a>
            <a href="{{ route('backend.tenants.index', array_merge($statusBaseQuery, ['status' => 'active'])) }}"
               class="rounded-2xl border bg-emerald-50/70 p-4 shadow-sm transition hover:shadow {{ $statusFilter === 'active' ? 'border-emerald-600 ring-1 ring-emerald-600' : 'border-emerald-200 hover:border-emerald-300' }}"
               data-testid="tenant-stat-active">
                <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">Aktif</p>
                <p class="mt-1 text-2xl font-bold text-emerald-700">{{ number_format($stats['active']) }}</p>
            </a>
            <a href="{{ route('backend.tenants.index', array_merge($statusBaseQuery, ['status' => 'suspended'])) }}"
               class="rounded-2xl border bg-rose-50/70 p-4 shadow-sm transition hover:shadow {{ $statusFilter === 'suspended' ? 'border-rose-600 ring-1 ring-rose-600' : 'border-rose-200 hover:border-rose-300' }}"
               data-testid="tenant-stat-suspended">
                <p class="text-xs font-medium uppercase tracking-wide text-rose-700">Suspended</p>
                <p class="mt-1 text-2xl font-bold text-rose-700">{{ number_format($stats['suspended']) }}</p>
            </a>
            <a href="{{ route('backend.tenants.index', array_merge($statusBaseQuery, ['status' => 'upgrade'])) }}"
               class="rounded-2xl border bg-amber-50/70 p-4 shadow-sm transition hover:shadow {{ $statusFilter === 'upgrade' ? 'border-amber-600 ring-1 ring-amber-600' : 'border-amber-200 hover:border-amber-300' }}"
               data-testid="tenant-stat-upgrade">
                <p class="text-xs font-medium uppercase tracking-wide text-amber-700">Menunggu Upgrade</p>
                <p class="mt-1 text-2xl font-bold text-amber-700">{{ number_format($stats['upgrade']) }}</p>
            </a>
        </div>

        {{-- Filter paket --}}
        <div class="flex flex-wrap items-center gap-2" data-testid="plan-filter-bar">
            <span class="text-xs font-semibold uppercase tracking-wide text-stone-500">Filter Paket:</span>
            @php
                $baseQuery = array_filter(['q' => $search, 'status' => $statusFilter], fn ($v) => $v !== '' && $v !== null);
            @endphp
            <a
                href="{{ route('backend.tenants.index', $baseQuery) }}"
                class="rounded-full border px-3 py-1 text-xs font-semibold {{ $planFilter === '' ? 'border-stone-900 bg-stone-900 text-white' : 'border-stone-300 bg-white text-stone-700 hover:bg-stone-50' }}"
                data-testid="plan-filter-all"
            >Semua</a>
            @foreach ($plans as $planOption)
                <a
                    href="{{ route('backend.tenants.index', array_merge($baseQuery, ['plan' => $planOption])) }}"
                    class="rounded-full border px-3 py-1 text-xs font-semibold uppercase {{ $planFilter === $planOption ? 'border-stone-900 bg-stone-900 text-white' : 'border-stone-300 bg-white text-stone-700 hover:bg-stone-50' }}"
                    data-testid="plan-filter-{{ $planOption }}"
                >{{ $planOption }}</a>
            @endforeach
            @if ($search !== '' || $planFilter !== '' || $statusFilter !== '')
                <a href="{{ route('backend.tenants.index') }}" class="rounded-full border border-dashed border-stone-300 px-3 py-1 text-xs font-medium text-stone-500 hover:bg-stone-50" data-testid="tenant-filter-reset">Reset filter</a>
            @endif
        </div>

        <div class="space-y-3" data-testid="tenant-list">
            @forelse($tenants as $tenant)
                @php
                    $subscription = $tenant->subscription;
                    $currentPlan = $subscription?->plan ?? 'free';
                    $expiredAt = $subscription?->expired_at;
                    $isFreePlan = $currentPlan === 'free';
                    $isExpired = $expiredAt && $expiredAt->isPast();
                    $isExpiringSoon = $expiredAt && ! $isExpired && $expiredAt->lte(now()->addDays(7));
                    $daysToExpiry = $isExpiringSoon ? max(0, (int) ceil(now()->diffInDays($expiredAt, false))) : null;
                @endphp
                <article class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm" data-testid="tenant-card-{{ $tenant->id }}">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-semibold text-stone-900">{{ $tenant->name }}</h3>
                                @if ($tenant->is_suspended)
                                    <span class="rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-700">SUSPENDED</span>
                                @else
                                    <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">AKTIF</span>
                                @endif
                                @if($currentPlan === 'free')
                                    <span class="rounded-full border border-gray-200 bg-gray-50 px-2 py-0.5 text-[11px] font-semibold text-gray-700" data-testid="tenant-plan-badge-{{ $tenant->id }}">FREE</span>
                                @elseif($currentPlan === 'pro')
                                    <span class="rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700" data-testid="tenant-plan-badge-{{ $tenant->id }}">PRO</span>
                                @elseif($currentPlan === 'premium')
                                    <span class="rounded-full border border-purple-200 bg-purple-50 px-2 py-0.5 text-[11px] font-semibold text-purple-700" data-testid="tenant-plan-badge-{{ $tenant->id }}">PREMIUM</span>
                                @else
                                    <span class="rounded-full border border-stone-200 bg-stone-50 px-2 py-0.5 text-[11px] font-semibold uppercase text-stone-700" data-testid="tenant-plan-badge-{{ $tenant->id }}">{{ $currentPlan }}</span>
                                @endif
                                @if($tenant->subscriptionUpgradeRequests->where('status', 'pending')->count() > 0)
                                    <span class="rounded-full border border-orange-200 bg-orange-50 px-2 py-0.5 text-[11px] font-semibold text-orange-700">Request Upgrade</span>
                                @endif
                            </div>
                            <p class="text-sm text-stone-600">{{ $tenant->email }}</p>

                            {{-- Masa berlaku langganan --}}
                            <div class="mt-2" data-testid="tenant-expiry-{{ $tenant->id }}">
                                @if($isFreePlan)
                                    <span class="inline-flex items-center gap-1 rounded-lg bg-stone-100 px-2 py-1 text-xs font-medium text-stone-600">Paket gratis — tanpa masa berlaku</span>
                                @elseif($isExpired)
                                    <span class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-2 py-1 text-xs font-semibold text-rose-700">Kadaluarsa sejak {{ $expiredAt->format('d M Y') }}</span>
                                @elseif($isExpiringSoon)
                                    <span class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">Segera berakhir {{ $expiredAt->format('d M Y') }} ({{ $daysToExpiry <= 0 ? 'hari ini' : $daysToExpiry.' hari lagi' }})</span>
                                @elseif($expiredAt)
                                    <span class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">Aktif s/d {{ $expiredAt->format('d M Y') }}</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-lg bg-stone-100 px-2 py-1 text-xs font-medium text-stone-600">Tanpa batas waktu</span>
                                @endif
                            </div>

                            <div class="mt-2 flex flex-wrap gap-2 text-xs text-stone-700">
                                <span class="rounded-lg bg-stone-100 px-2 py-1">Role: {{ $tenant->role }}</span>
                                <span class="rounded-lg bg-stone-100 px-2 py-1">Layanan: {{ $tenant->services_count }}</span>
                                <span class="rounded-lg bg-stone-100 px-2 py-1">Pelanggan: {{ $tenant->customers_count }}</span>
                                <span class="rounded-lg bg-stone-100 px-2 py-1">Booking: {{ $tenant->bookings_count }}</span>
                                <span class="rounded-lg bg-stone-100 px-2 py-1">Kuota Terpakai: {{ $subscription?->bookings_consumed_total ?? 0 }}</span>
                                <span class="rounded-lg bg-stone-100 px-2 py-1">Daftar: {{ $tenant->created_at?->format('d M Y') }}</span>
                            </div>
                            @if ($tenant->is_suspended && $tenant->suspended_reason)
                                <p class="mt-2 text-xs text-rose-700">Alasan suspend: {{ $tenant->suspended_reason }}</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                @click="open({ name: @js($tenant->name), role: @js($tenant->role), plan: @js($currentPlan), action: @js(route('backend.tenants.quick-update', $tenant)) })"
                                class="rounded-xl bg-stone-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black"
                                data-testid="tenant-quick-edit-button-{{ $tenant->id }}"
                            >
                                Ubah Paket
                            </button>

                            @if ($tenant->is_suspended)
                                <form method="POST" action="{{ route('backend.tenants.suspend.update', $tenant) }}" onsubmit="return confirm('Aktifkan kembali tenant {{ $tenant->name }}?')">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_suspended" value="0">
                                    <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" data-testid="tenant-activate-button-{{ $tenant->id }}">
                                        Aktifkan
                                    </button>
                                </form>
                            @else
                                <button
                                    type="button"
                                    @click="openSuspend({ name: @js($tenant->name), action: @js(route('backend.tenants.suspend.update', $tenant)) })"
                                    class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100"
                                    data-testid="tenant-suspend-button-{{ $tenant->id }}"
                                >
                                    Suspend
                                </button>
                            @endif

                            <a href="{{ route('backend.tenants.edit', $tenant) }}" class="rounded-xl border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50" data-testid="tenant-detail-link-{{ $tenant->id }}">
                                Kelola Detail
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-stone-300 bg-stone-50 p-6 text-sm text-stone-600" data-testid="tenant-empty-state">
                    Tenant tidak ditemukan.
                </div>
            @endforelse
        </div>

        <div>
            {{ $tenants->links() }}
        </div>

        {{-- Modal edit cepat --}}
        <div
            x-show="show"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center px-4"
            data-testid="quick-edit-modal"
        >
            <div class="absolute inset-0 bg-stone-900/60" @click="close()"></div>
            <div
                x-show="show"
                x-transition
                class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-stone-900">Ubah Paket &amp; Role</h3>
                        <p class="mt-1 text-sm text-stone-600" x-text="form.name"></p>
                    </div>
                    <button type="button" @click="close()" class="rounded-lg p-1 text-stone-400 hover:bg-stone-100 hover:text-stone-700" data-testid="quick-edit-close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form :action="form.action" method="POST" class="mt-5 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-sm font-medium text-stone-700">Role</label>
                        <select
                            name="role"
                            x-model="form.role"
                            class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm capitalize focus:border-stone-500 focus:outline-none focus:ring-0"
                            data-testid="quick-edit-role-select"
                        >
                            @foreach ($roles as $roleOption)
                                <option value="{{ $roleOption }}">{{ ucfirst($roleOption) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700">Paket Langganan</label>
                        <select
                            name="plan"
                            x-model="form.plan"
                            class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm uppercase focus:border-stone-500 focus:outline-none focus:ring-0"
                            data-testid="quick-edit-plan-select"
                        >
                            @foreach ($plans as $planOption)
                                <option value="{{ $planOption }}">{{ strtoupper($planOption) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="close()" class="rounded-xl border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50" data-testid="quick-edit-cancel">Batal</button>
                        <button type="submit" class="rounded-xl bg-stone-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black" data-testid="quick-edit-save">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal suspend --}}
        <div
            x-show="showSuspend"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center px-4"
            data-testid="suspend-modal"
        >
            <div class="absolute inset-0 bg-stone-900/60" @click="closeSuspend()"></div>
            <div x-show="showSuspend" x-transition class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-stone-900">Suspend Tenant</h3>
                        <p class="mt-1 text-sm text-stone-600" x-text="suspendForm.name"></p>
                    </div>
                    <button type="button" @click="closeSuspend()" class="rounded-lg p-1 text-stone-400 hover:bg-stone-100 hover:text-stone-700" data-testid="suspend-close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form :action="suspendForm.action" method="POST" class="mt-5 space-y-4">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="is_suspended" value="1">
                    <div>
                        <label class="block text-sm font-medium text-stone-700">Alasan Suspend</label>
                        <textarea name="suspended_reason" rows="3" placeholder="Contoh: pelanggaran kebijakan / tunggakan pembayaran" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm focus:border-stone-500 focus:outline-none focus:ring-0" data-testid="suspend-reason-input"></textarea>
                        <p class="mt-1 text-xs text-stone-500">Sesi login tenant akan langsung dihentikan.</p>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="closeSuspend()" class="rounded-xl border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50" data-testid="suspend-cancel">Batal</button>
                        <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700" data-testid="suspend-confirm">Suspend Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script>
        function tenantManager() {
            return {
                show: false,
                showSuspend: false,
                form: { name: '', role: 'tenant', plan: 'free', action: '' },
                suspendForm: { name: '', action: '' },
                open(data) {
                    this.form = { ...data };
                    this.show = true;
                },
                close() {
                    this.show = false;
                },
                openSuspend(data) {
                    this.suspendForm = { ...data };
                    this.showSuspend = true;
                },
                closeSuspend() {
                    this.showSuspend = false;
                },
            };
        }
    </script>
    <style>[x-cloak]{display:none!important;}</style>
</x-backend-layout>
