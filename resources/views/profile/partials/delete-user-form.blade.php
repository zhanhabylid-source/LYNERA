<section class="space-y-4">
    <header>
        <h3 class="text-xl font-semibold text-rose-700">Hapus Akun</h3>
        <p class="mt-1 text-sm leading-6 text-rose-700/90">
            Tindakan ini permanen dan akan menghapus seluruh data tenant (layanan, pelanggan, booking, pembayaran).
        </p>
    </header>

    <button
        x-data
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="touch-target rounded-xl border border-rose-300 bg-white px-5 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-100"
    >
        Hapus Akun Permanen
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-stone-900">
                Konfirmasi Hapus Akun
            </h2>

            <p class="mt-2 text-sm leading-6 text-stone-600">
                Masukkan password Anda untuk konfirmasi penghapusan akun tenant secara permanen.
            </p>

            <div class="mt-5">
                <label for="password" class="text-sm font-semibold text-stone-700">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="field-control touch-target"
                    placeholder="Masukkan password"
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <button type="button" x-on:click="$dispatch('close')" class="touch-target rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm font-semibold text-stone-700 hover:bg-stone-50">
                    Batal
                </button>

                <button type="submit" class="touch-target rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">
                    Ya, Hapus Akun
                </button>
            </div>
        </form>
    </x-modal>
</section>

