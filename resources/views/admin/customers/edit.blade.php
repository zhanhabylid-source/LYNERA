<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ubah Pelanggan</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="space-y-4" data-testid="customer-edit-form">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama</label>
                        <input type="text" name="name" value="{{ old('name', $customer->name) }}" class="mt-1 w-full rounded-md border-gray-300" data-testid="customer-name-input">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Telepon</label>
                            <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="mt-1 w-full rounded-md border-gray-300" data-testid="customer-phone-input">
                            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="mt-1 w-full rounded-md border-gray-300" data-testid="customer-email-input">
                            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Instagram</label>
                        <input type="text" name="instagram" value="{{ old('instagram', $customer->instagram) }}" class="mt-1 w-full rounded-md border-gray-300">
                        @error('instagram') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('admin.customers.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700">Batal</a>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white rounded-md hover:bg-rose-700" data-testid="customer-submit-button">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>


