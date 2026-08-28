<x-guest-layout>
    <div class="mx-auto w-full max-w-md">
        <div class="rounded-[2rem] border border-rose-100 bg-white/95 p-8 shadow-2xl">
            <div class="mb-6 space-y-2 text-center">
                <p class="text-sm uppercase tracking-[0.24em] text-rose-600">Atur Ulang Kata Sandi</p>
                <h2 class="text-3xl font-semibold text-stone-900">Buat kata sandi baru</h2>
                <p class="text-sm leading-6 text-stone-500">Masukkan kata sandi baru untuk akun Anda.</p>
            </div>

            <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-stone-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                        class="w-full rounded-3xl border border-stone-200 bg-rose-50/50 px-4 py-3 text-sm text-stone-900 outline-none transition focus:border-rose-300 focus:ring-4 focus:ring-rose-100"
                        data-testid="reset-email-input">
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs" />
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-stone-700">Kata Sandi Baru</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password"
                        class="w-full rounded-3xl border border-stone-200 bg-rose-50/50 px-4 py-3 text-sm text-stone-900 outline-none transition focus:border-rose-300 focus:ring-4 focus:ring-rose-100"
                        data-testid="reset-password-input">
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs" />
                </div>

                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-medium text-stone-700">Konfirmasi Kata Sandi</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                        class="w-full rounded-3xl border border-stone-200 bg-rose-50/50 px-4 py-3 text-sm text-stone-900 outline-none transition focus:border-rose-300 focus:ring-4 focus:ring-rose-100"
                        data-testid="reset-password-confirm-input">
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-xs" />
                </div>

                <button type="submit" class="w-full rounded-3xl bg-stone-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-black" data-testid="reset-submit">
                    Simpan Kata Sandi Baru
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
