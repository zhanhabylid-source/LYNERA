<section>
    <header>
        <h3 class="text-xl font-semibold text-stone-900">Keamanan Akun</h3>
        <p class="mt-1 text-sm leading-6 text-stone-600">
            Gunakan password yang kuat untuk menjaga keamanan data pelanggan dan transaksi tenant.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-4">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="text-sm font-semibold text-stone-700">Password Saat Ini</label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" class="field-control touch-target" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="update_password_password" class="text-sm font-semibold text-stone-700">Password Baru</label>
                <input id="update_password_password" name="password" type="password" autocomplete="new-password" class="field-control touch-target" />
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
            </div>
            <div>
                <label for="update_password_password_confirmation" class="text-sm font-semibold text-stone-700">Konfirmasi Password Baru</label>
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="field-control touch-target" />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <button type="submit" class="touch-target rounded-xl bg-stone-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-black">
                Update Password
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2400)"
                    class="text-sm font-medium text-emerald-700"
                >
                    Password berhasil diperbarui.
                </p>
            @endif
        </div>
    </form>
</section>

