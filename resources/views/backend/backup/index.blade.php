<x-backend-layout>
    <section class="space-y-5">
        <div>
            <h2 class="text-2xl font-semibold text-stone-900">Backup Data Tenant</h2>
            <p class="mt-1 text-sm text-stone-600">
                Unduh, jadwalkan, dan pulihkan seluruh data tenant. Backup terjadwal berjalan setiap hari pukul 02:00
                dan menyimpan file <code class="rounded bg-stone-100 px-1 text-xs">.zip</code> lengkap dengan file unggahan.
            </p>
        </div>

        @if(session('success'))
            <div class="hidden" data-testid="backup-flash-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="hidden" data-testid="backup-flash-error">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" data-testid="backup-flash-errors">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ================= RINGKASAN + UNDUH ================= --}}
        <article class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex-1">
                    <p class="text-sm text-stone-500">Total Tenant Terdata</p>
                    <p class="mt-1 text-3xl font-bold text-stone-900" data-testid="backup-tenant-count">{{ number_format($tenantCount) }}</p>
                    <p class="mt-2 text-sm text-stone-600">
                        {{ $tables->count() }} tabel · {{ number_format($totalRows) }} baris data siap dibackup.
                    </p>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row lg:flex-col xl:flex-row">
                    <a
                        href="{{ route('backend.backup.download') }}"
                        download
                        data-no-loading
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-stone-300 bg-white px-4 py-3 text-sm font-semibold text-stone-800 hover:bg-stone-50"
                        data-testid="backup-download-json-button"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" /></svg>
                        Unduh (.json)
                    </a>
                    <a
                        href="{{ route('backend.backup.download-zip') }}"
                        download
                        data-no-loading
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-stone-900 px-4 py-3 text-sm font-semibold text-white hover:bg-black"
                        data-testid="backup-download-zip-button"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Unduh Lengkap (.zip)
                    </a>
                </div>
            </div>
        </article>

        {{-- ================= RESTORE ================= --}}
        <article class="rounded-2xl border border-rose-200 bg-rose-50/50 p-6 shadow-sm" data-testid="backup-restore-card">
            <div class="flex items-start gap-3">
                <div class="rounded-full bg-rose-100 p-2 text-rose-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v6h6M20 20v-6h-6M4 10a8 8 0 0114-4M20 14a8 8 0 01-14 4"/></svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-rose-900">Pulihkan dari Backup</h3>
                    <p class="mt-1 text-sm text-rose-800">
                        Unggah file <strong>.json</strong> atau <strong>.zip</strong> hasil backup untuk memulihkan seluruh data.
                        <strong>Aksi ini bersifat destruktif</strong> — data saat ini akan ditimpa oleh data dari backup.
                        Akun super admin Anda tetap dipertahankan agar Anda tidak keluar sesi.
                    </p>

                    <form method="POST" action="{{ route('backend.backup.restore') }}" enctype="multipart/form-data" class="mt-4 space-y-3" data-testid="backup-restore-form">
                        @csrf

                        <div>
                            <label for="backup_file" class="block text-sm font-medium text-rose-900">File backup</label>
                            <input
                                type="file"
                                name="backup_file"
                                id="backup_file"
                                accept=".json,.zip,application/json,application/zip"
                                required
                                class="mt-1 block w-full rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm text-stone-900 file:mr-3 file:rounded-lg file:border-0 file:bg-rose-600 file:px-3 file:py-2 file:text-white hover:file:bg-rose-700"
                                data-testid="backup-restore-file-input"
                            >
                            <p class="mt-1 text-xs text-stone-500">Maksimal 100 MB.</p>
                        </div>

                        <div>
                            <label for="confirm" class="block text-sm font-medium text-rose-900">
                                Ketik <code class="rounded bg-rose-100 px-1 text-xs font-bold">PULIHKAN</code> untuk konfirmasi
                            </label>
                            <input
                                type="text"
                                name="confirm"
                                id="confirm"
                                placeholder="PULIHKAN"
                                required
                                autocomplete="off"
                                class="mt-1 block w-full rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm text-stone-900 placeholder-stone-400 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                data-testid="backup-restore-confirm-input"
                            >
                        </div>

                        <button
                            type="submit"
                            onclick="return confirm('Data saat ini akan ditimpa oleh isi backup. Lanjutkan?')"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 px-5 py-3 text-sm font-semibold text-white hover:bg-rose-700"
                            data-testid="backup-restore-submit-button"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                            Pulihkan Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </article>

        {{-- ================= RIWAYAT BACKUP TERJADWAL ================= --}}
        <article class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm" data-testid="backup-history-card">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-stone-900">Riwayat Backup Terjadwal</h3>
                    <p class="mt-1 text-sm text-stone-600">Backup otomatis harian tersimpan di <code class="rounded bg-stone-100 px-1 text-xs">storage/app/backups/</code>. File > 14 hari akan dihapus otomatis.</p>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-stone-200 text-sm">
                    <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">
                        <tr>
                            <th class="px-4 py-2">Nama File</th>
                            <th class="px-4 py-2">Format</th>
                            <th class="px-4 py-2">Ukuran</th>
                            <th class="px-4 py-2">Waktu</th>
                            <th class="px-4 py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse($backups as $b)
                            <tr data-testid="backup-history-row">
                                <td class="px-4 py-3 font-mono text-xs text-stone-700">{{ $b['name'] }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-lg bg-stone-100 px-2 py-0.5 text-xs font-medium uppercase text-stone-700">{{ $b['ext'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-stone-700">{{ $b['size_human'] }}</td>
                                <td class="px-4 py-3 text-stone-600">{{ $b['modified_human'] }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a
                                            href="{{ route('backend.backup.stored.download', ['filename' => $b['name']]) }}"
                                            download
                                            data-no-loading
                                            class="rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-800 hover:bg-stone-50"
                                            data-testid="backup-history-download"
                                        >Unduh</a>
                                        <form method="POST" action="{{ route('backend.backup.stored.destroy', ['filename' => $b['name']]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                onclick="return confirm('Hapus backup ini?')"
                                                class="rounded-lg border border-rose-300 bg-white px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-50"
                                                data-testid="backup-history-delete"
                                            >Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-stone-500" data-testid="backup-history-empty">
                                    Belum ada backup terjadwal. Backup otomatis pertama akan dibuat malam ini pukul 02:00.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        {{-- ================= DAFTAR TABEL ================= --}}
        <article class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-stone-900">Tabel yang Dicadangkan</h3>
            <div class="mt-4 flex flex-wrap gap-2" data-testid="backup-table-list">
                @foreach($tables as $table)
                    <span class="rounded-lg bg-stone-100 px-2.5 py-1 text-xs font-medium text-stone-700">{{ $table }}</span>
                @endforeach
            </div>
        </article>

        <article class="rounded-2xl border border-amber-200 bg-amber-50/70 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-amber-800">Catatan</h3>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-amber-800">
                <li><strong>.zip</strong> berisi <code class="rounded bg-amber-100 px-1 text-xs">data.json</code> + folder <code class="rounded bg-amber-100 px-1 text-xs">storage/</code> (bukti pembayaran, logo, QR).</li>
                <li>Notifikasi email backup terjadwal dikirim ke seluruh super admin bila <code class="rounded bg-amber-100 px-1 text-xs">MAIL_MAILER</code> sudah diatur (mis. <code class="rounded bg-amber-100 px-1 text-xs">resend</code>).</li>
                <li>Sebelum <strong>Pulihkan</strong>, unduh backup terkini agar bisa rollback jika terjadi masalah.</li>
                <li>Restore mengembalikan seluruh tabel — akun tenant lama yang tidak ada di backup akan hilang.</li>
            </ul>
        </article>
    </section>
</x-backend-layout>
