<x-guest-layout>
    <div class="mx-auto w-full max-w-md">
        <div class="rounded-[2rem] border border-rose-100 bg-white/95 p-8 shadow-2xl">
            <div class="mb-6 space-y-2 text-center">
                <p class="text-sm uppercase tracking-[0.24em] text-rose-600">Lupa Kata Sandi</p>
                <h2 class="text-3xl font-semibold text-stone-900">Atur ulang akses Anda</h2>
                <p class="text-sm leading-6 text-stone-500">Masukkan email akun Anda, kami akan mengirimkan tautan untuk membuat kata sandi baru.</p>
            </div>

            <x-auth-session-status class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-stone-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" inputmode="email" placeholder="nama@email.com"
                        class="w-full rounded-3xl border border-stone-200 bg-rose-50/50 px-4 py-3 text-sm text-stone-900 outline-none transition focus:border-rose-300 focus:ring-4 focus:ring-rose-100"
                        data-testid="forgot-email-input">
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs" />
                </div>

                <button type="submit" class="w-full rounded-3xl bg-stone-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-black" data-testid="forgot-submit">
                    Kirim Tautan Reset Kata Sandi
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-stone-500">
                Ingat kata sandi Anda?
                <a href="{{ route('login') }}" class="font-semibold text-rose-600 hover:text-rose-700" data-testid="forgot-back-to-login">Kembali ke login</a>
            </p>
        </div>
    </div>
</x-guest-layout>
