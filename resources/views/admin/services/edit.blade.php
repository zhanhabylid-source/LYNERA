<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-stone-900">Ubah Layanan</h2>
    </x-slot>

    <div class="py-5 sm:py-8">
        <div class="mx-auto w-full max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-rose-100 bg-white p-4 shadow-sm sm:p-6">
                <form method="POST" action="{{ route('admin.services.update', $service) }}" class="space-y-4 sm:space-y-5" data-testid="service-edit-form">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-stone-700">Nama</label>
                        <input type="text" name="name" value="{{ old('name', $service->name) }}" class="mt-1 w-full rounded-xl border-stone-300 text-sm focus:border-rose-400 focus:ring-rose-300" data-testid="service-name-input">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-stone-700">Harga</label>
                            <input type="number" step="0.01" name="price" value="{{ old('price', $service->price) }}" class="mt-1 w-full rounded-xl border-stone-300 text-sm focus:border-rose-400 focus:ring-rose-300" data-testid="service-price-input">
                            @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700">Durasi (menit)</label>
                            <input type="number" name="duration" value="{{ old('duration', $service->duration) }}" class="mt-1 w-full rounded-xl border-stone-300 text-sm focus:border-rose-400 focus:ring-rose-300" data-testid="service-duration-input">
                            @error('duration') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700">Deskripsi</label>
                        <textarea name="description" rows="4" class="mt-1 w-full rounded-xl border-stone-300 text-sm focus:border-rose-400 focus:ring-rose-300">{{ old('description', $service->description) }}</textarea>
                        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-2 sm:flex sm:justify-end sm:gap-3">
                        <a href="{{ route('admin.services.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700">Batal</a>
                        <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700" data-testid="service-submit-button">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

