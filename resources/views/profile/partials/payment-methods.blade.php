<section class="rounded-2xl border border-rose-200 bg-white p-5 shadow-sm sm:p-6" data-testid="tenant-payment-methods-section">
    <h3 class="text-xl font-semibold text-stone-900" data-testid="tenant-payment-methods-title">Tambah Metode Pembayaran</h3>

    @if(session('status') && str_starts_with((string) session('status'), 'payment-method-'))
        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" data-testid="tenant-payment-method-success">Metode pembayaran berhasil diperbarui.</div>
    @endif
    @if($errors->has('payment_method'))
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first('payment_method') }}</div>
    @endif

    <div class="mt-5 grid gap-5 xl:grid-cols-[0.9fr_1.1fr]">
        <form method="POST" action="{{ route('profile.payment-methods.store') }}" enctype="multipart/form-data" class="rounded-2xl border border-stone-200 bg-stone-50 p-4" x-data="{ type: '{{ old('type', 'bank') }}' }" data-testid="tenant-payment-method-create-form">
            @csrf
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="text-xs font-semibold text-stone-700">Jenis
                    <select name="type" x-model="type" class="mt-1 w-full rounded-xl border border-stone-300 bg-white px-3 py-2.5" data-testid="tenant-payment-method-create-type">
                        <option value="bank">Bank Transfer</option>
                        <option value="ewallet">E-Wallet</option>
                        <option value="qris">QRIS</option>
                    </select>
                </label>
                <label class="text-xs font-semibold text-stone-700">Nama Bank / Provider
                    <input type="text" name="bank_name" value="{{ old('bank_name') }}" required placeholder="BCA, GoPay, QRIS Studio" class="mt-1 w-full rounded-xl border border-stone-300 bg-white px-3 py-2.5" data-testid="tenant-payment-method-create-provider">
                </label>
                <label class="text-xs font-semibold text-stone-700">Nama Pemilik
                    <input type="text" name="account_name" value="{{ old('account_name') }}" placeholder="Nama studio atau pemilik" class="mt-1 w-full rounded-xl border border-stone-300 bg-white px-3 py-2.5" data-testid="tenant-payment-method-create-account-name">
                </label>
                <label class="text-xs font-semibold text-stone-700">Nomor Rekening / E-Wallet
                    <input type="text" name="account_number" value="{{ old('account_number') }}" placeholder="Tidak wajib untuk QRIS" class="mt-1 w-full rounded-xl border border-stone-300 bg-white px-3 py-2.5" data-testid="tenant-payment-method-create-account-number">
                </label>
                <label class="text-xs font-semibold text-stone-700">Kontak Konfirmasi
                    <input type="text" name="contact" value="{{ old('contact') }}" placeholder="WhatsApp atau email" class="mt-1 w-full rounded-xl border border-stone-300 bg-white px-3 py-2.5" data-testid="tenant-payment-method-create-contact">
                </label>
                <label class="text-xs font-semibold text-stone-700">Urutan
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" max="999" class="mt-1 w-full rounded-xl border border-stone-300 bg-white px-3 py-2.5" data-testid="tenant-payment-method-create-order">
                </label>
                <label class="text-xs font-semibold text-stone-700 sm:col-span-2" x-show="type === 'qris'" x-cloak>Gambar QRIS
                    <input type="file" name="qr_code" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="mt-1 w-full rounded-xl border border-stone-300 bg-white px-3 py-2.5" data-testid="tenant-payment-method-create-qr">
                </label>
                <label class="text-xs font-semibold text-stone-700 sm:col-span-2">Catatan
                    <textarea name="notes" rows="2" placeholder="Instruksi khusus untuk pelanggan" class="mt-1 w-full rounded-xl border border-stone-300 bg-white px-3 py-2.5" data-testid="tenant-payment-method-create-notes">{{ old('notes') }}</textarea>
                </label>
                <div class="flex flex-wrap gap-4 sm:col-span-2">
                    <label class="inline-flex items-center gap-2 text-xs font-semibold text-stone-700"><input type="checkbox" name="is_active" value="1" checked data-testid="tenant-payment-method-create-active"> Aktif</label>
                    <label class="inline-flex items-center gap-2 text-xs font-semibold text-stone-700"><input type="checkbox" name="is_primary" value="1" data-testid="tenant-payment-method-create-primary"> Jadikan utama</label>
                </div>
            </div>
            <button type="submit" class="mt-4 w-full rounded-xl bg-stone-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-black" data-testid="tenant-payment-method-create-submit">Tambah Metode</button>
        </form>

        <div class="space-y-3" data-testid="tenant-payment-method-list">
            @forelse($user->paymentAccounts as $method)
                <article class="rounded-2xl border {{ $method->is_primary ? 'border-rose-300 bg-rose-50/50' : 'border-stone-200 bg-white' }} p-4" data-testid="tenant-payment-method-card-{{ $method->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-semibold text-stone-900" data-testid="tenant-payment-method-provider-{{ $method->id }}">{{ $method->bank_name }}</p>
                                <span class="rounded-full bg-stone-100 px-2 py-1 text-[10px] font-semibold text-stone-600">{{ $method->typeLabel() }}</span>
                                @if($method->is_primary)<span class="rounded-full bg-rose-100 px-2 py-1 text-[10px] font-semibold text-rose-700" data-testid="tenant-payment-method-primary-badge-{{ $method->id }}">Utama</span>@endif
                                <span class="rounded-full px-2 py-1 text-[10px] font-semibold {{ $method->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-200 text-stone-600' }}" data-testid="tenant-payment-method-status-{{ $method->id }}">{{ $method->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </div>
                            @if($method->account_name)<p class="mt-2 text-sm text-stone-600">{{ $method->account_name }}</p>@endif
                            @if($method->account_number)<p class="text-sm font-bold text-stone-900">{{ $method->account_number }}</p>@endif
                            @if($method->contact)<p class="mt-1 text-xs text-stone-500">Konfirmasi: {{ $method->contact }}</p>@endif
                        </div>
                        @if($method->qrCodeUrl())
                            <img src="{{ $method->qrCodeUrl() }}" alt="QRIS {{ $method->bank_name }}" class="h-24 w-24 rounded-xl border border-stone-200 bg-white object-contain p-1" data-testid="tenant-payment-method-qr-{{ $method->id }}">
                        @endif
                    </div>
                    @if($method->notes)<p class="mt-3 rounded-xl bg-stone-50 p-3 text-xs leading-relaxed text-stone-600">{{ $method->notes }}</p>@endif

                    <div class="mt-4 flex flex-wrap gap-2">
                        @if(!$method->is_primary && $method->is_active)
                            <form method="POST" action="{{ route('profile.payment-methods.primary', $method) }}">@csrf @method('PATCH')<button type="submit" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700" data-testid="tenant-payment-method-primary-button-{{ $method->id }}">Jadikan Utama</button></form>
                        @endif
                        <form method="POST" action="{{ route('profile.payment-methods.toggle', $method) }}">@csrf @method('PATCH')<button type="submit" class="rounded-xl border border-stone-300 bg-white px-3 py-2 text-xs font-semibold text-stone-700" data-testid="tenant-payment-method-toggle-button-{{ $method->id }}">{{ $method->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                        <form method="POST" action="{{ route('profile.payment-methods.destroy', $method) }}" onsubmit="return confirm('Hapus metode pembayaran ini?')">@csrf @method('DELETE')<button type="submit" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700" data-testid="tenant-payment-method-delete-button-{{ $method->id }}">Hapus</button></form>
                    </div>

                    <details class="mt-4 rounded-xl border border-stone-200 bg-white p-3">
                        <summary class="cursor-pointer text-xs font-semibold text-stone-700" data-testid="tenant-payment-method-edit-toggle-{{ $method->id }}">Edit Metode</summary>
                        <form method="POST" action="{{ route('profile.payment-methods.update', $method) }}" enctype="multipart/form-data" class="mt-3 grid gap-3 sm:grid-cols-2" x-data="{ type: '{{ $method->type }}' }">
                            @csrf @method('PUT')
                            <select name="type" x-model="type" class="rounded-xl border border-stone-300 px-3 py-2 text-sm"><option value="bank">Bank Transfer</option><option value="ewallet">E-Wallet</option><option value="qris">QRIS</option></select>
                            <input type="text" name="bank_name" value="{{ $method->bank_name }}" required class="rounded-xl border border-stone-300 px-3 py-2 text-sm" aria-label="Nama bank atau provider">
                            <input type="text" name="account_name" value="{{ $method->account_name }}" class="rounded-xl border border-stone-300 px-3 py-2 text-sm" aria-label="Nama pemilik">
                            <input type="text" name="account_number" value="{{ $method->account_number }}" class="rounded-xl border border-stone-300 px-3 py-2 text-sm" aria-label="Nomor rekening atau e-wallet">
                            <input type="text" name="contact" value="{{ $method->contact }}" class="rounded-xl border border-stone-300 px-3 py-2 text-sm" aria-label="Kontak konfirmasi">
                            <input type="number" name="sort_order" value="{{ $method->sort_order }}" min="0" max="999" class="rounded-xl border border-stone-300 px-3 py-2 text-sm" aria-label="Urutan">
                            <input type="file" name="qr_code" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="rounded-xl border border-stone-300 px-3 py-2 text-sm sm:col-span-2" x-show="type === 'qris'" aria-label="Ganti gambar QRIS">
                            <textarea name="notes" rows="2" class="rounded-xl border border-stone-300 px-3 py-2 text-sm sm:col-span-2" aria-label="Catatan">{{ $method->notes }}</textarea>
                            <div class="flex gap-4 sm:col-span-2"><label class="text-xs"><input type="checkbox" name="is_active" value="1" @checked($method->is_active)> Aktif</label><label class="text-xs"><input type="checkbox" name="is_primary" value="1" @checked($method->is_primary)> Utama</label></div>
                            <button type="submit" class="rounded-xl bg-stone-900 px-4 py-2 text-xs font-semibold text-white sm:col-span-2" data-testid="tenant-payment-method-edit-submit-{{ $method->id }}">Simpan Perubahan</button>
                        </form>
                    </details>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-stone-300 bg-stone-50 p-5 text-sm text-stone-600" data-testid="tenant-payment-method-empty">Belum ada metode pembayaran. Tambahkan bank, e-wallet, atau QRIS agar muncul di invoice pelanggan.</div>
            @endforelse
        </div>
    </div>
</section>