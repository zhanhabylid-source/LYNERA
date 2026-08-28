<x-backend-layout>
    <section class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-600">Billing LYNERA</p>
            <h2 class="mt-1 text-2xl font-semibold text-stone-900" data-testid="payment-methods-title">Metode Pembayaran Upgrade</h2>
            <p class="mt-1 max-w-2xl text-sm text-stone-600">Atur rekening bank, e-wallet, dan QRIS yang ditampilkan kepada seluruh MUA saat membayar upgrade paket.</p>
        </div>

        <article class="rounded-2xl border border-rose-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold text-stone-900">Tambah Metode Pembayaran</h3>
            <form method="POST" action="{{ route('backend.payment-methods.store') }}" enctype="multipart/form-data" class="mt-4 grid gap-4 md:grid-cols-2" x-data="{ type: '{{ old('type', 'bank') }}' }" data-testid="payment-method-create-form">
                @csrf
                <label class="text-sm font-medium text-stone-700">
                    Jenis
                    <select name="type" x-model="type" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2.5" data-testid="payment-method-create-type">
                        <option value="bank">Bank Transfer</option>
                        <option value="ewallet">E-Wallet</option>
                        <option value="qris">QRIS</option>
                    </select>
                </label>
                <label class="text-sm font-medium text-stone-700">
                    Nama Bank / Provider
                    <input type="text" name="provider_name" value="{{ old('provider_name') }}" required placeholder="Contoh: BCA, GoPay, QRIS LYNERA" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2.5" data-testid="payment-method-create-provider">
                </label>
                <label class="text-sm font-medium text-stone-700">
                    Nama Pemilik
                    <input type="text" name="account_name" value="{{ old('account_name') }}" placeholder="Contoh: PT LYNERA Indonesia" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2.5" data-testid="payment-method-create-account-name">
                </label>
                <label class="text-sm font-medium text-stone-700">
                    Nomor Rekening / E-Wallet
                    <input type="text" name="account_number" value="{{ old('account_number') }}" placeholder="Tidak wajib untuk QRIS" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2.5" data-testid="payment-method-create-account-number">
                </label>
                <label class="text-sm font-medium text-stone-700">
                    Kontak Konfirmasi
                    <input type="text" name="contact" value="{{ old('contact') }}" placeholder="WhatsApp atau email" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2.5" data-testid="payment-method-create-contact">
                </label>
                <label class="text-sm font-medium text-stone-700">
                    Urutan
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" max="999" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2.5" data-testid="payment-method-create-order">
                </label>
                <label class="text-sm font-medium text-stone-700 md:col-span-2" x-show="type === 'qris'" x-cloak>
                    Gambar QRIS
                    <input type="file" name="qr_code" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="mt-1 w-full rounded-xl border border-stone-300 bg-white px-3 py-2.5" data-testid="payment-method-create-qr">
                </label>
                <label class="text-sm font-medium text-stone-700 md:col-span-2">
                    Instruksi Pembayaran
                    <textarea name="instructions" rows="3" placeholder="Contoh: Transfer sesuai nominal paket dan unggah bukti pembayaran." class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2.5" data-testid="payment-method-create-instructions">{{ old('instructions') }}</textarea>
                </label>
                <div class="flex flex-wrap items-center gap-4 md:col-span-2">
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-stone-700">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-stone-300 text-rose-600 focus:ring-rose-500" data-testid="payment-method-create-active">
                        Aktifkan metode
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-stone-700">
                        <input type="checkbox" name="is_primary" value="1" @checked(old('is_primary', false)) class="rounded border-stone-300 text-rose-600 focus:ring-rose-500" data-testid="payment-method-create-primary">
                        Jadikan utama
                    </label>
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="rounded-xl bg-stone-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-black" data-testid="payment-method-create-submit">Tambah Metode</button>
                </div>
            </form>
        </article>

        <div class="grid gap-4 lg:grid-cols-2" data-testid="payment-method-list">
            @forelse($paymentMethods as $method)
                <article class="rounded-2xl border {{ $method->is_primary ? 'border-rose-300 bg-rose-50/40' : 'border-stone-200 bg-white' }} p-5 shadow-sm" data-testid="payment-method-card-{{ $method->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-semibold text-stone-900" data-testid="payment-method-provider-{{ $method->id }}">{{ $method->provider_name }}</h3>
                                <span class="rounded-full bg-stone-100 px-2 py-1 text-[11px] font-semibold text-stone-700">{{ $method->typeLabel() }}</span>
                                @if($method->is_primary)
                                    <span class="rounded-full bg-rose-100 px-2 py-1 text-[11px] font-semibold text-rose-700" data-testid="payment-method-primary-badge-{{ $method->id }}">Utama</span>
                                @endif
                                <span class="rounded-full px-2 py-1 text-[11px] font-semibold {{ $method->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-200 text-stone-600' }}" data-testid="payment-method-status-{{ $method->id }}">{{ $method->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </div>
                            <p class="mt-2 text-sm text-stone-700">{{ $method->account_name ?: '-' }}</p>
                            <p class="text-sm font-semibold text-stone-900">{{ $method->account_number ?: 'Gunakan QRIS' }}</p>
                            @if($method->contact)
                                <p class="mt-1 text-xs text-stone-500">Kontak: {{ $method->contact }}</p>
                            @endif
                        </div>
                        @if($method->qrCodeUrl())
                            <img src="{{ $method->qrCodeUrl() }}" alt="QRIS {{ $method->provider_name }}" class="h-24 w-24 rounded-xl border border-stone-200 bg-white object-contain p-1" data-testid="payment-method-qr-{{ $method->id }}">
                        @endif
                    </div>

                    @if($method->instructions)
                        <p class="mt-3 rounded-xl bg-white/80 p-3 text-xs leading-relaxed text-stone-600">{{ $method->instructions }}</p>
                    @endif

                    <div class="mt-4 flex flex-wrap gap-2">
                        @if(! $method->is_primary && $method->is_active)
                            <form method="POST" action="{{ route('backend.payment-methods.primary', $method) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100" data-testid="payment-method-primary-button-{{ $method->id }}">Jadikan Utama</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('backend.payment-methods.toggle', $method) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="rounded-xl border border-stone-300 bg-white px-3 py-2 text-xs font-semibold text-stone-700 hover:bg-stone-50" data-testid="payment-method-toggle-button-{{ $method->id }}">{{ $method->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        </form>
                        <form method="POST" action="{{ route('backend.payment-methods.destroy', $method) }}" onsubmit="return confirm('Hapus metode pembayaran ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100" data-testid="payment-method-delete-button-{{ $method->id }}">Hapus</button>
                        </form>
                    </div>

                    <details class="mt-4 rounded-xl border border-stone-200 bg-white p-4">
                        <summary class="cursor-pointer text-sm font-semibold text-stone-800" data-testid="payment-method-edit-toggle-{{ $method->id }}">Edit Metode</summary>
                        <form method="POST" action="{{ route('backend.payment-methods.update', $method) }}" enctype="multipart/form-data" class="mt-4 grid gap-3 sm:grid-cols-2" x-data="{ type: '{{ $method->type }}' }">
                            @csrf
                            @method('PUT')
                            <label class="text-xs font-medium text-stone-600">Jenis
                                <select name="type" x-model="type" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2" data-testid="payment-method-edit-type-{{ $method->id }}">
                                    <option value="bank">Bank Transfer</option>
                                    <option value="ewallet">E-Wallet</option>
                                    <option value="qris">QRIS</option>
                                </select>
                            </label>
                            <label class="text-xs font-medium text-stone-600">Nama Bank / Provider
                                <input type="text" name="provider_name" value="{{ $method->provider_name }}" required class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2" data-testid="payment-method-edit-provider-{{ $method->id }}">
                            </label>
                            <label class="text-xs font-medium text-stone-600">Nama Pemilik
                                <input type="text" name="account_name" value="{{ $method->account_name }}" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2">
                            </label>
                            <label class="text-xs font-medium text-stone-600">Nomor Rekening / E-Wallet
                                <input type="text" name="account_number" value="{{ $method->account_number }}" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2">
                            </label>
                            <label class="text-xs font-medium text-stone-600">Kontak
                                <input type="text" name="contact" value="{{ $method->contact }}" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2">
                            </label>
                            <label class="text-xs font-medium text-stone-600">Urutan
                                <input type="number" name="sort_order" value="{{ $method->sort_order }}" min="0" max="999" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2">
                            </label>
                            <label class="text-xs font-medium text-stone-600 sm:col-span-2" x-show="type === 'qris'">Ganti Gambar QRIS
                                <input type="file" name="qr_code" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2">
                            </label>
                            <label class="text-xs font-medium text-stone-600 sm:col-span-2">Instruksi
                                <textarea name="instructions" rows="2" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2">{{ $method->instructions }}</textarea>
                            </label>
                            <div class="flex flex-wrap gap-4 sm:col-span-2">
                                <label class="inline-flex items-center gap-2 text-xs font-medium text-stone-700"><input type="checkbox" name="is_active" value="1" @checked($method->is_active)> Aktif</label>
                                <label class="inline-flex items-center gap-2 text-xs font-medium text-stone-700"><input type="checkbox" name="is_primary" value="1" @checked($method->is_primary)> Utama</label>
                            </div>
                            <button type="submit" class="rounded-xl bg-stone-900 px-4 py-2 text-xs font-semibold text-white hover:bg-black sm:col-span-2" data-testid="payment-method-edit-submit-{{ $method->id }}">Simpan Perubahan</button>
                        </form>
                    </details>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-stone-300 bg-white p-6 text-sm text-stone-600 lg:col-span-2" data-testid="payment-method-empty-state">Belum ada metode pembayaran upgrade. Tambahkan minimal satu metode aktif agar MUA dapat membayar upgrade paket.</div>
            @endforelse
        </div>
    </section>
</x-backend-layout>