<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-stone-800 leading-tight">Wizard Pengaturan Awal</h2>
    </x-slot>

    @php
        $profileDone = !empty(auth()->user()->name);
        $serviceDone = $servicesCount > 0;
        $customerDone = $customersCount > 0;
        $bookingDone = $bookingsCount > 0;
        $completedSteps = collect([$profileDone, $serviceDone, $customerDone, $bookingDone])->filter()->count();
        $progressPercent = (int) round(($completedSteps / 4) * 100);
    @endphp

    <style>[x-cloak]{display:none!important;}</style>

    <div class="py-8 bg-gradient-to-b from-rose-50 via-amber-50 to-white min-h-screen">
        <div
            class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-5"
            x-data="onboardingWizard({
                initialStep: {{ (int) $initialStep }},
                profileDone: @js($profileDone),
                serviceDone: @js($serviceDone),
                customerDone: @js($customerDone),
                bookingDone: @js($bookingDone),
                clearDraftStep: @js(session()->has('success') ? max(0, ((int) session('onboarding_step', 0)) - 1) : 0)
            })"
            x-init="init()"
        >
            @if (session('success'))
                <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="p-4 rounded-xl border border-amber-200 bg-amber-50 text-amber-700">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="p-4 rounded-xl border border-red-200 bg-red-50 text-red-700">
                    <p class="font-semibold">Mohon perbaiki hal berikut:</p>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-md border border-rose-100 p-6 md:p-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h3 class="text-2xl font-bold text-stone-900">Selamat datang, {{ auth()->user()->name }}!</h3>
                        <p class="mt-2 text-stone-600">Selesaikan langkah berikut untuk menuntaskan pengaturan LYNERA Anda.</p>
                    </div>
                    <div class="rounded-xl border border-rose-100 bg-rose-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-stone-500">Paket Saat Ini</p>
                        <p class="text-lg font-bold text-rose-700 uppercase">{{ $plan }}</p>
                        <p class="text-xs text-stone-600 mt-1">{{ $planBenefit }}</p>
                        <p class="text-xs text-stone-600 mt-1">{{ $planLimitLabel }}</p>
                        <p class="text-xs text-stone-600 mt-1">
                            Total booking terpakai: {{ $bookingUsage['bookings_count'] }}
                            @if(! $bookingUsage['is_unlimited'])
                                / {{ $bookingUsage['limit'] }} (sisa {{ $bookingUsage['remaining'] }})
                            @endif
                        </p>
                    </div>
                </div>

                <div class="mt-5 rounded-xl border border-stone-200 bg-stone-50 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-stone-800">Progres Onboarding</p>
                        <p class="text-sm font-semibold text-rose-700">{{ $progressPercent }}%</p>
                    </div>
                    <div class="mt-2 h-2.5 w-full rounded-full bg-stone-200">
                        <div
                            class="h-2.5 rounded-full bg-rose-500 transition-all duration-500"
                            style="width: {{ $progressPercent }}%;"
                            aria-label="Bilah progres onboarding"
                        ></div>
                    </div>
                    <p class="mt-2 text-xs text-stone-600">{{ $completedSteps }} dari 4 langkah selesai.</p>
                </div>

                @if ($bookingDone)
                    <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-700">
                        Onboarding selesai. Workspace Anda sudah siap.
                    </div>
                @endif

                <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                    <button type="button" @click="openStep(1)" :class="step === 1 ? 'ring-2 ring-rose-400' : ''" class="rounded-xl border p-3 text-left {{ $profileDone ? 'border-emerald-200 bg-emerald-50' : 'border-stone-200 bg-white' }}">
                        <p class="font-semibold">Langkah 1</p>
                        <p class="text-stone-600">Profil</p>
                    </button>
                    <button type="button" @click="openStep(2)" :disabled="!canAccessStep(2)" :class="step === 2 ? 'ring-2 ring-rose-400' : ''" class="rounded-xl border p-3 text-left {{ $serviceDone ? 'border-emerald-200 bg-emerald-50' : 'border-stone-200 bg-white' }}" :class="{ 'opacity-60 cursor-not-allowed': !canAccessStep(2) }">
                        <p class="font-semibold">Langkah 2</p>
                        <p class="text-stone-600">Layanan Pertama</p>
                    </button>
                    <button type="button" @click="openStep(3)" :disabled="!canAccessStep(3)" :class="step === 3 ? 'ring-2 ring-rose-400' : ''" class="rounded-xl border p-3 text-left {{ $customerDone ? 'border-emerald-200 bg-emerald-50' : 'border-stone-200 bg-white' }}" :class="{ 'opacity-60 cursor-not-allowed': !canAccessStep(3) }">
                        <p class="font-semibold">Langkah 3</p>
                        <p class="text-stone-600">Pelanggan Pertama</p>
                    </button>
                    <button type="button" @click="openStep(4)" :disabled="!canAccessStep(4)" :class="step === 4 ? 'ring-2 ring-rose-400' : ''" class="rounded-xl border p-3 text-left {{ $bookingDone ? 'border-emerald-200 bg-emerald-50' : 'border-stone-200 bg-white' }}" :class="{ 'opacity-60 cursor-not-allowed': !canAccessStep(4) }">
                        <p class="font-semibold">Langkah 4</p>
                        <p class="text-stone-600">Booking Pertama</p>
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-rose-100 p-6">
                <div x-show="step === 1" x-cloak>
                    <form method="POST" action="{{ route('onboarding.profile') }}" class="space-y-4" data-onboarding-form data-draft-scope="1">
                        @csrf
                        <h4 class="text-lg font-semibold text-stone-900">Langkah 1: Pengaturan Profil</h4>
                        <div>
                            <label for="name" class="block text-sm font-medium text-stone-700">Nama Tampil</label>
                            <input id="name" name="name" type="text" value="{{ old('name', auth()->user()->name) }}" required class="field-control">
                        </div>
                        <div>
                            <label for="studio_name" class="block text-sm font-medium text-stone-700">Nama Studio (Opsional)</label>
                            <input id="studio_name" name="studio_name" type="text" value="{{ old('studio_name', auth()->user()->studio_name) }}" class="field-control">
                        </div>
                        <div>
                            <label for="studio_location" class="block text-sm font-medium text-stone-700">Alamat Studio (Opsional)</label>
                            <input id="studio_location" name="studio_location" type="text" value="{{ old('studio_location', auth()->user()->studio_location) }}" class="field-control">
                        </div>
                        <div>
                            <label for="studio_maps_link" class="block text-sm font-medium text-stone-700">Tautan Google Maps (Opsional)</label>
                            <input id="studio_maps_link" name="studio_maps_link" type="url" value="{{ old('studio_maps_link', auth()->user()->studio_maps_link) }}" class="field-control">
                        </div>
                        <div class="flex items-center justify-between gap-3 pt-2">
                            <span class="text-xs text-stone-500">Simpan langkah ini, lalu lanjutkan.</span>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="openStep(2)" class="rounded-xl border border-stone-300 px-4 py-2 text-sm text-stone-700 hover:bg-stone-50 min-h-[44px] flex items-center">Lanjut</button>
                                <button type="submit" class="rounded-xl bg-rose-500 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-600 min-h-[44px] flex items-center">Simpan 1</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div x-show="step === 2" x-cloak>
                    <form method="POST" action="{{ route('onboarding.service') }}" class="space-y-4" data-onboarding-form data-draft-scope="2">
                        @csrf
                        <h4 class="text-lg font-semibold text-stone-900">Langkah 2: Buat Layanan Pertama</h4>
                        <div>
                            <label for="service_name" class="block text-sm font-medium text-stone-700">Nama Layanan</label>
                            <input id="service_name" name="name" type="text" value="{{ old('name') }}" placeholder="Bridal Makeup" required class="field-control">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="price" class="block text-sm font-medium text-stone-700">Harga</label>
                                <input id="price" name="price" type="number" min="0" step="1000" value="{{ old('price') }}" required class="field-control">
                            </div>
                            <div>
                                <label for="duration" class="block text-sm font-medium text-stone-700">Durasi (menit)</label>
                                <input id="duration" name="duration" type="number" min="1" step="1" value="{{ old('duration') }}" required class="field-control">
                            </div>
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-stone-700">Deskripsi</label>
                            <textarea id="description" name="description" rows="3" class="field-control">{{ old('description') }}</textarea>
                        </div>
                        <div class="flex items-center justify-between gap-3 pt-2">
                            <button type="button" @click="openStep(1)" class="rounded-xl border border-stone-300 px-4 py-2 text-sm text-stone-700 hover:bg-stone-50 min-h-[44px] flex items-center">Kembali</button>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="openStep(3)" class="rounded-xl border border-stone-300 px-4 py-2 text-sm text-stone-700 hover:bg-stone-50 min-h-[44px] flex items-center">Lanjut</button>
                                <button type="submit" class="rounded-xl bg-rose-500 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-600 min-h-[44px] flex items-center">Simpan 2</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div x-show="step === 3" x-cloak>
                    <form method="POST" action="{{ route('onboarding.customer') }}" class="space-y-4" data-onboarding-form data-draft-scope="3">
                        @csrf
                        <h4 class="text-lg font-semibold text-stone-900">Langkah 3: Buat Pelanggan Pertama</h4>
                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-stone-700">Nama Pelanggan</label>
                            <input id="customer_name" name="name" type="text" value="{{ old('name') }}" required class="field-control">
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-stone-700">Telepon</label>
                            <input id="phone" name="phone" type="text" value="{{ old('phone') }}" required class="field-control">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="customer_email" class="block text-sm font-medium text-stone-700">Email</label>
                                <input id="customer_email" name="email" type="email" value="{{ old('email') }}" class="field-control">
                            </div>
                            <div>
                                <label for="instagram" class="block text-sm font-medium text-stone-700">Instagram</label>
                                <input id="instagram" name="instagram" type="text" value="{{ old('instagram') }}" class="field-control">
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-3 pt-2">
                            <button type="button" @click="openStep(2)" class="rounded-xl border border-stone-300 px-4 py-2 text-sm text-stone-700 hover:bg-stone-50 min-h-[44px] flex items-center">Kembali</button>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="openStep(4)" class="rounded-xl border border-stone-300 px-4 py-2 text-sm text-stone-700 hover:bg-stone-50 min-h-[44px] flex items-center" :disabled="!canAccessStep(4)" :class="{ 'opacity-60 cursor-not-allowed': !canAccessStep(4) }">Lanjut</button>
                                <button type="submit" class="rounded-xl bg-rose-500 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-600 min-h-[44px] flex items-center">Simpan 3</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div x-show="step === 4" x-cloak>
                    <form method="POST" action="{{ route('onboarding.booking') }}" class="space-y-4" data-onboarding-form data-draft-scope="4">
                        @csrf
                        <h4 class="text-lg font-semibold text-stone-900">Langkah 4: Buat Booking Pertama</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="customer_id" class="block text-sm font-medium text-stone-700">Pelanggan</label>
                                <select id="customer_id" name="customer_id" class="field-control" required>
                                    <option value="">Pilih pelanggan</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                            {{ $customer->name }} ({{ $customer->phone }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="service_id" class="block text-sm font-medium text-stone-700">Layanan</label>
                                <select id="service_id" name="service_id" class="field-control" required>
                                    <option value="">Pilih layanan</option>
                                    @foreach ($services as $service)
                                        <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>
                                            {{ $service->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="booking_date" class="block text-sm font-medium text-stone-700">Tanggal</label>
                                <input id="booking_date" name="booking_date" type="date" value="{{ old('booking_date', now()->addDay()->format('Y-m-d')) }}" required class="field-control">
                            </div>
                            <div>
                                <label for="booking_time" class="block text-sm font-medium text-stone-700">Jam</label>
                                <input id="booking_time" name="booking_time" type="time" value="{{ old('booking_time', '10:00') }}" required class="field-control">
                            </div>
                        </div>
                        <div>
                            <label for="location" class="block text-sm font-medium text-stone-700">Lokasi</label>
                            <input id="location" name="location" type="text" value="{{ old('location') }}" class="field-control" placeholder="Alamat home service atau tautan maps">
                        </div>
                        <div>
                            <label for="notes" class="block text-sm font-medium text-stone-700">Catatan</label>
                            <textarea id="notes" name="notes" rows="3" class="field-control">{{ old('notes') }}</textarea>
                        </div>
                        <input type="hidden" name="status" value="pending">
                        <div class="flex items-center justify-between gap-3 pt-2">
                            <button type="button" @click="openStep(3)" class="rounded-xl border border-stone-300 px-4 py-2 text-sm text-stone-700 hover:bg-stone-50">Kembali</button>
                            <button type="submit" class="rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600 {{ ($servicesCount === 0 || $customersCount === 0) ? 'opacity-60 cursor-not-allowed' : '' }}" {{ ($servicesCount === 0 || $customersCount === 0) ? 'disabled' : '' }}>
                                Selesai
                            </button>
                        </div>
                        @if ($servicesCount === 0 || $customersCount === 0)
                            <p class="text-xs text-amber-700">Buat minimal satu layanan dan satu pelanggan sebelum membuat booking pertama.</p>
                        @endif
                    </form>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-rose-100 p-4 flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-stone-900">Masa aktif paket: Tanpa batas waktu</p>
                    <p class="text-xs text-stone-600">Paket Free dibatasi maksimal 10 booking total.</p>
                </div>
                <a href="{{ route('billing.index') }}" class="px-4 py-2 rounded-xl border border-stone-300 text-sm text-stone-700 hover:bg-stone-50">Tagihan</a>
            </div>
        </div>
    </div>

    <script>
        function onboardingWizard(config) {
            return {
                step: Number(config.initialStep || 1),
                storageKey: 'lynera:onboarding-step',
                profileDone: Boolean(config.profileDone),
                serviceDone: Boolean(config.serviceDone),
                customerDone: Boolean(config.customerDone),
                bookingDone: Boolean(config.bookingDone),
                clearDraftStep: Number(config.clearDraftStep || 0),
                draftPrefix: 'lynera:onboarding-draft:',
                init() {
                    const persisted = parseInt(localStorage.getItem(this.storageKey) || '', 10);
                    if (Number.isInteger(persisted)) {
                        this.step = this.resolveStep(persisted);
                    } else {
                        this.step = this.resolveStep(this.step);
                    }

                    this.$watch('step', (value) => {
                        localStorage.setItem(this.storageKey, String(this.resolveStep(value)));
                    });

                    if (this.clearDraftStep >= 1 && this.clearDraftStep <= 4) {
                        localStorage.removeItem(this.getDraftKey(this.clearDraftStep));
                    }

                    this.bindDraftPersistence();
                },
                canAccessStep(target) {
                    if (target <= 1) {
                        return true;
                    }
                    if (target === 2) {
                        return this.profileDone;
                    }
                    if (target === 3) {
                        return this.profileDone && this.serviceDone;
                    }

                    return this.profileDone && this.serviceDone && this.customerDone;
                },
                resolveStep(target) {
                    const normalized = Math.min(Math.max(parseInt(target, 10) || 1, 1), 4);
                    if (this.canAccessStep(normalized)) {
                        return normalized;
                    }

                    if (!this.profileDone) {
                        return 1;
                    }
                    if (!this.serviceDone) {
                        return 2;
                    }
                    if (!this.customerDone) {
                        return 3;
                    }

                    return 4;
                },
                openStep(target) {
                    this.step = this.resolveStep(target);
                },
                getDraftKey(scope) {
                    return `${this.draftPrefix}${scope}`;
                },
                bindDraftPersistence() {
                    const forms = document.querySelectorAll('[data-onboarding-form][data-draft-scope]');

                    forms.forEach((form) => {
                        const scope = form.getAttribute('data-draft-scope');
                        const draftKey = this.getDraftKey(scope);
                        const fields = form.querySelectorAll('input[name], select[name], textarea[name]');

                        const existingDraftRaw = localStorage.getItem(draftKey);
                        if (existingDraftRaw) {
                            try {
                                const existingDraft = JSON.parse(existingDraftRaw);
                                fields.forEach((field) => {
                                    if (!(field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement)) {
                                        return;
                                    }

                                    const name = field.name || '';
                                    if (name === '' || name.startsWith('_') || !(name in existingDraft)) {
                                        return;
                                    }

                                    if (field instanceof HTMLInputElement && (field.type === 'checkbox' || field.type === 'radio')) {
                                        // Keep server-side checked state as source of truth after validation.
                                        return;
                                    }

                                    if (field.value !== '' && field.value !== null) {
                                        return;
                                    }

                                    field.value = String(existingDraft[name] ?? '');
                                });
                            } catch (error) {
                                localStorage.removeItem(draftKey);
                            }
                        }

                        const persistDraft = () => {
                            const payload = {};
                            fields.forEach((field) => {
                                if (!(field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement)) {
                                    return;
                                }

                                const name = field.name || '';
                                if (name === '' || name.startsWith('_')) {
                                    return;
                                }

                                if (field instanceof HTMLInputElement && field.type === 'file') {
                                    return;
                                }

                                if (field instanceof HTMLInputElement && (field.type === 'checkbox' || field.type === 'radio')) {
                                    payload[name] = field.checked;
                                    return;
                                }

                                payload[name] = field.value ?? '';
                            });

                            localStorage.setItem(draftKey, JSON.stringify(payload));
                        };

                        fields.forEach((field) => {
                            field.addEventListener('input', persistDraft);
                            field.addEventListener('change', persistDraft);
                        });

                        form.addEventListener('submit', () => {
                            persistDraft();
                        });
                    });
                },
            };
        }

        document.addEventListener('DOMContentLoaded', () => {
            const firstInvalid = document.querySelector('input:invalid, select:invalid, textarea:invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalid.focus({ preventScroll: true });
            }
        });
    </script>
</x-app-layout>

