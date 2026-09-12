<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">Edit Tautan Booking</h2>
            <a
                href="{{ route('admin.booking-links.index') }}"
                class="px-4 py-2 rounded-xl border border-stone-300 text-stone-700 hover:bg-stone-50 min-h-[44px] flex items-center justify-center"
            >
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-rose-50 via-amber-50 to-white min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-rose-100 shadow p-6">
                <div class="mb-5">
                    <h3 class="text-lg font-semibold text-stone-900">Pengaturan Tautan Booking</h3>
                    <p class="text-sm text-stone-600 mt-1">
                        Ubah layanan atau pengaturan tanpa membuat link baru. URL/token tetap sama.
                    </p>
                    <p class="text-xs text-stone-500 mt-2 break-all">
                        {{ route('public.booking.show', $form->token) }}
                    </p>
                </div>

                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.booking-links.update', $form) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-stone-700">
                            Layanan yang diizinkan
                        </label>

                        <div class="mt-2 max-h-72 overflow-y-auto rounded-xl border border-stone-300 bg-white p-3 space-y-2">
                            @forelse($services as $service)
                                <label class="flex items-center gap-3 rounded-lg px-2 py-2 hover:bg-rose-50">
                                    <input
                                        type="checkbox"
                                        name="service_ids[]"
                                        value="{{ $service->id }}"
                                        class="rounded border-stone-300 text-rose-600 focus:ring-rose-400"
                                        @checked(in_array(
                                            (int) $service->id,
                                            old('service_ids', $selectedServiceIds),
                                            true
                                        ))
                                    >
                                    <span class="text-sm text-stone-700">{{ $service->name }}</span>
                                </label>
                            @empty
                                <p class="text-sm text-stone-500">
                                    Belum ada layanan. Tambahkan layanan dulu.
                                </p>
                            @endforelse
                        </div>

                        <p class="mt-1 text-xs text-stone-500">
                            Centang layanan baru tanpa perlu membuat link baru.
                        </p>

                        @error('service_ids')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                        @error('service_ids.*')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-stone-700">
                                Maksimal pengiriman (opsional)
                            </label>
                            <input
                                type="number"
                                min="1"
                                max="500"
                                name="max_submissions"
                                value="{{ old('max_submissions', $form->max_submissions) }}"
                                class="mt-1 w-full rounded-xl border-stone-300 focus:border-rose-400 focus:ring-rose-300"
                            >
                            <p class="mt-1 text-xs text-stone-500">
                                Saat ini sudah {{ $form->submission_count }} pengiriman.
                            </p>
                            @error('max_submissions')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700">
                                Biaya Transport Default Link
                            </label>
                            <input
                                type="number"
                                min="0"
                                step="1000"
                                name="transport_fee"
                                value="{{ old('transport_fee', $transportFee) }}"
                                class="mt-1 w-full rounded-xl border-stone-300 focus:border-rose-400 focus:ring-rose-300"
                            >
                            <p class="mt-1 text-xs text-stone-500">
                                Dipakai sebagai default Home Service.
                            </p>
                            @error('transport_fee')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700">Judul T&C</label>
                        <input
                            type="text"
                            name="terms_title"
                            value="{{ old('terms_title', $termsTitle) }}"
                            required
                            class="mt-1 w-full rounded-xl border-stone-300 focus:border-rose-400 focus:ring-rose-300"
                        >
                        @error('terms_title')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700">Isi T&C</label>
                        <textarea
                            name="terms_content"
                            rows="7"
                            required
                            class="mt-1 w-full rounded-xl border-stone-300 focus:border-rose-400 focus:ring-rose-300"
                        >{{ old('terms_content', $termsContent) }}</textarea>
                        @error('terms_content')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-wrap gap-2 pt-2">
                        <button
                            type="submit"
                            class="px-6 py-2.5 rounded-xl bg-rose-500 text-white hover:bg-rose-600 transition min-h-[44px]"
                        >
                            Simpan Perubahan
                        </button>

                        <a
                            href="{{ route('public.booking.show', $form->token) }}"
                            target="_blank"
                            class="px-6 py-2.5 rounded-xl bg-stone-800 text-white hover:bg-black min-h-[44px] flex items-center justify-center"
                        >
                            Buka Link
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
