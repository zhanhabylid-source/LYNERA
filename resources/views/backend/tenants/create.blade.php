<x-backend-layout>
    <section class="space-y-5">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-stone-900">Buat Tenant Baru</h2>
                <p class="mt-1 text-sm text-stone-600">Gunakan form ini untuk membuat subscriber tenant baru.</p>
            </div>
            <a href="{{ route('backend.tenants.index') }}" class="rounded-xl border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50">
                Kembali
            </a>
        </div>

        <form method="POST" action="{{ route('backend.tenants.store') }}" class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <label class="text-sm font-medium text-stone-700">
                    Nama
                    <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                </label>
                <label class="text-sm font-medium text-stone-700">
                    Email
                    <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                </label>
                <label class="text-sm font-medium text-stone-700">
                    Role
                    <select name="role" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                        @foreach($roles as $role)
                            <option value="{{ $role }}" @selected(old('role', 'tenant') === $role)>{{ strtoupper($role) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-sm font-medium text-stone-700">
                    Plan
                    <select name="plan" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                        @foreach($plans as $plan)
                            <option value="{{ $plan }}" @selected(old('plan', 'free') === $plan)>{{ strtoupper($plan) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-sm font-medium text-stone-700">
                    Password
                    <input type="password" name="password" required class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                </label>
                <label class="text-sm font-medium text-stone-700">
                    Konfirmasi Password
                    <input type="password" name="password_confirmation" required class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                </label>
            </div>
            <div class="mt-5">
                <button type="submit" class="rounded-xl bg-stone-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-black">
                    Simpan Tenant
                </button>
            </div>
        </form>
    </section>
</x-backend-layout>

