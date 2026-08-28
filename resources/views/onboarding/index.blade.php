<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-stone-800 leading-tight">Pengaturan Awal</h2>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-rose-50 via-amber-50 to-white min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-4 rounded-xl bg-emerald-100 text-emerald-700 border border-emerald-200">{{ session('success') }}</div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 rounded-xl bg-white border border-rose-100 shadow">
                    <p class="text-sm text-stone-500">Profil</p>
                    <p class="text-lg font-semibold text-stone-900">Langkah 1</p>
                </div>
                <div class="p-4 rounded-xl bg-white border border-rose-100 shadow">
                    <p class="text-sm text-stone-500">Layanan</p>
                    <p class="text-lg font-semibold text-stone-900">Langkah 2</p>
                </div>
                <div class="p-4 rounded-xl bg-white border border-rose-100 shadow">
                    <p class="text-sm text-stone-500">Booking</p>
                    <p class="text-lg font-semibold text-stone-900">Langkah 3</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-rose-100 shadow p-6">
                <h3 class="text-lg font-semibold text-stone-900">1. Atur Profil</h3>
                <form class="mt-4 flex gap-3" method="POST" action="{{ route('onboarding.profile') }}">
                    @csrf
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" class="w-full rounded-lg border-stone-300">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-500 text-white hover:bg-rose-600">Simpan</button>
                </form>
            </div>

            <div class="bg-white rounded-2xl border border-rose-100 shadow p-6">
                <h3 class="text-lg font-semibold text-stone-900">2. Tambah Layanan Pertama</h3>
                @if($servicesCount > 0)
                    <p class="mt-2 text-sm text-emerald-700">Anda sudah memiliki {{ $servicesCount }} layanan.</p>
                @else
                    <form method="POST" action="{{ route('onboarding.service') }}" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                        @csrf
                        <input type="text" name="name" placeholder="Nama layanan" class="rounded-lg border-stone-300">
                        <input type="number" step="0.01" name="price" placeholder="Harga" class="rounded-lg border-stone-300">
                        <input type="number" name="duration" placeholder="Durasi (menit)" class="rounded-lg border-stone-300">
                        <input type="text" name="description" placeholder="Deskripsi" class="rounded-lg border-stone-300">
                        <button type="submit" class="md:col-span-2 px-5 py-2.5 rounded-xl bg-amber-500 text-white hover:bg-amber-600">Buat</button>
                    </form>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-rose-100 shadow p-6">
                <h3 class="text-lg font-semibold text-stone-900">3. Buat Booking Pertama</h3>
                @if($bookingsCount > 0)
                    <p class="mt-2 text-sm text-emerald-700">Mantap! Anda sudah membuat booking pertama.</p>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex mt-4 px-5 py-2.5 rounded-xl bg-emerald-500 text-white hover:bg-emerald-600">
                        Ke Dasbor
                    </a>
                @else
                    <p class="mt-2 text-sm text-stone-600">Buat pelanggan terlebih dahulu dari halaman Pelanggan jika daftar masih kosong.</p>
                    <form method="POST" action="{{ route('onboarding.booking') }}" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                        @csrf
                        <select name="customer_id" class="rounded-lg border-stone-300">
                            <option value="">Pilih pelanggan</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                        <select name="service_id" class="rounded-lg border-stone-300">
                            <option value="">Pilih layanan</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                            @endforeach
                        </select>
                        <input type="date" name="booking_date" class="rounded-lg border-stone-300">
                        <input type="time" name="booking_time" class="rounded-lg border-stone-300">
                        <input type="text" name="location" placeholder="Lokasi" class="rounded-lg border-stone-300">
                        <select name="status" class="rounded-lg border-stone-300">
                            <option value="pending">Menunggu</option>
                            <option value="confirmed">Dikonfirmasi</option>
                            <option value="completed">Selesai</option>
                            <option value="canceled">Dibatalkan</option>
                        </select>
                        <textarea name="notes" class="md:col-span-2 rounded-lg border-stone-300" placeholder="Catatan"></textarea>
                        <button type="submit" class="md:col-span-2 px-5 py-2.5 rounded-xl bg-rose-500 text-white hover:bg-rose-600">
                            Buat Booking
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>


