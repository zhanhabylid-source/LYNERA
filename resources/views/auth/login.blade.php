<x-guest-layout>
    <div class="mx-auto grid min-h-[calc(100vh-4rem)] w-full max-w-6xl items-center gap-6 px-4 sm:px-6 lg:grid-cols-2 lg:gap-10 lg:px-8">
    <div class="mb-8 lg:mb-0">
        <div class="inline-flex items-center gap-3 rounded-3xl border border-rose-100 bg-white/80 px-4 py-3 shadow-sm shadow-rose-100">
            <span class="inline-flex h-12 w-12 items-enter justify-center rounded-2xl bg-rose-50 text-rose-700 shadow-sm">
                <x-application-logo class="h-7 w-auto" />
            </span>
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-rose-600">LYNERA</p>
                <p class="text-xs text-stone-500">Effortless Beauty Business Management</p>
            </div>
        </div>

        <div class="mt-10 max-w-xl space-y-6">
            <h1 class="text-4xl font-semibold tracking-tight text-stone-900 sm:text-5xl">
                Selamat datang di LYNERA
            </h1>
            <p class="max-w-xl text-base leading-8 text-stone-600">
                Kendalikan booking, jadwal, pelanggan, dan pembayaran dari satu dashboard yang cantik dan mudah digunakan.
            </p>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-3xl border border-rose-100 bg-white/85 p-5 shadow-sm">
                    <p class="text-sm font-semibold text-stone-800">✔ Manage bookings effortlessly</p>
                </div>
                <div class="rounded-3xl border border-rose-100 bg-white/85 p-5 shadow-sm">
                    <p class="text-sm font-semibold text-stone-800">✔ Smart beauty calendar</p>
                </div>
                <div class="rounded-3xl border border-rose-100 bg-white/85 p-5 shadow-sm sm:col-span-2">
                    <p class="text-sm font-semibold text-stone-800">✔ Track clients & payments</p>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-rose-200 via-rose-100 to-amber-100 p-8 shadow-xl">
                <div class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-white/40 blur-2xl"></div>
                <div class="absolute -left-6 bottom-10 h-24 w-24 rounded-full bg-amber-200/40 blur-2xl"></div>
                <div class="relative z-10 space-y-3">
                    <p class="text-sm uppercase tracking-[0.24em] text-stone-700">Premium beauty workflow</p>
                    <h2 class="text-3xl font-semibold text-stone-900">Melayani pelanggan Anda dengan lebih tenang.</h2>
                    <p class="text-sm leading-6 text-stone-700">Lihat semua booking, konfirmasi jadwal, dan pantau pendapatan dalam satu tampilan yang elegan.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="w-full max-w-md mx-auto lg:mx-0">
        <div class="rounded-[2rem] border border-rose-100 bg-white/95 p-8 shadow-2xl">
            <div class="mb-6 space-y-2 text-center">
                <p class="text-sm uppercase tracking-[0.24em] text-rose-600">Login ke akun Anda</p>
                <h2 class="text-3xl font-semibold text-stone-900">Masuk dan mulai berkarya</h2>
                <p class="text-sm leading-6 text-stone-500">Masukkan email dan password untuk mengelola jadwal makeup Anda.</p>
            </div>

            <x-auth-session-status class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="js-auth-form space-y-5">
                @csrf

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-stone-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" inputmode="email" placeholder="nama@email.com"
                        class="w-full rounded-3xl border border-stone-200 bg-rose-50/50 px-4 py-3 text-sm text-stone-900 outline-none transition focus:border-rose-300 focus:ring-4 focus:ring-rose-100">
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs" />
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-stone-700">Password</label>
                            <div class="relative">
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                            class="w-full rounded-3xl border border-stone-200 bg-rose-50/50 px-4 py-3 pr-28 text-sm text-stone-900 outline-none transition focus:border-rose-300 focus:ring-4 focus:ring-rose-100">
                        <button type="button" data-toggle-password="password"
                            class="absolute inset-y-0 right-3 inline-flex items-center rounded-full px-3 text-xs font-semibold uppercase tracking-[0.18em] text-stone-500 transition hover:text-stone-700">
                            TAMPILKAN
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs" />
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-stone-600">
                        <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4 rounded border-stone-300 text-rose-600 focus:ring-rose-400">
                        Ingat saya
                    </label>
                    @if (Route::has('password.request'))
                        <a class="text-sm font-semibold text-rose-600 hover:text-rose-700" href="{{ route('password.request') }}">
                            Lupa password?
                        </a>
                    @endif
                </div>

                <button type="submit" data-loading-target class="w-full rounded-3xl bg-amber-500 px-6 py-3.5 text-sm font-semibold uppercase tracking-[0.18em] text-white shadow-lg shadow-amber-200 transition hover:bg-amber-600">
                    Masuk</button>

                <p class="text-center text-sm text-stone-500">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="font-semibold text-rose-600 hover:text-rose-700">Mulai uji coba gratis</a>
                </p>
            </form>

            <div class="mt-7 rounded-3xl border border-amber-100 bg-amber-50/80 p-4 text-sm text-stone-700">
                <p class="font-semibold">Mudah, aman, dan cepat.</p>
                <p class="mt-1">Masuk untuk melihat jadwal Anda hari ini dan menerima booking tanpa repot.</p>
            </div>
        </div>
    </div>
</div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-toggle-password]').forEach((button) => {
                button.addEventListener('click', () => {
                    const target = document.getElementById(button.getAttribute('data-toggle-password'));
                    if (!target) return;
                    const nextType = target.type === 'password' ? 'text' : 'password';
                    target.type = nextType;
                    button.textContent = nextType === 'password' ? 'SEMBUNYIKAN' : 'TAMPILKAN';
                });
            });

            document.querySelectorAll('.js-auth-form').forEach((form) => {
                form.addEventListener('submit', () => {
                    const submit = form.querySelector('[data-loading-target]');
                    if (!submit) return;
                    submit.disabled = true;
                    submit.textContent = 'Masuk...';
                    submit.classList.add('opacity-70', 'cursor-not-allowed');
                });
            });
        });
    </script>
</x-guest-layout>
