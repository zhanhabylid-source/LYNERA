<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Booking Publik</title>
    @include('partials.vite-assets')
</head>
<body class="font-sans bg-gradient-to-b from-rose-50 via-amber-50 to-white min-h-screen">
    @php
        $terms = is_array($effectiveTerms ?? null) ? $effectiveTerms : [];
        $termsTitle = trim((string) ($terms['title'] ?? 'Syarat & Ketentuan Booking'));
        $termsContent = trim((string) ($terms['content'] ?? ''));
    @endphp
    <main class="py-6 sm:py-10 px-4 sm:px-6">
        <div class="w-full max-w-2xl mx-auto">
            <div class="overflow-hidden rounded-2xl border border-rose-100 bg-white p-4 shadow-xl sm:p-6 lg:p-7">
                <h1 class="text-2xl font-semibold text-stone-900">Formulir Booking</h1>
                <p class="mt-1 text-sm text-stone-600">Silakan lengkapi formulir berikut. Tautan ini akan kedaluwarsa pada {{ $form->expires_at->format('d M Y H:i') }}.</p>

                @if (session('success'))
                    <div class="mt-4 p-3 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->has('booking'))
                    <div class="mt-4 p-3 rounded-xl border border-red-200 bg-red-50 text-red-700 text-sm">
                        {{ $errors->first('booking') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('public.booking.store', $token) }}" class="mt-5 space-y-4" data-testid="public-booking-form">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-stone-700">Nama</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-xl border-stone-300 focus:border-rose-400 focus:ring-rose-300" data-testid="public-booking-name-input">
                        @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-stone-700">Telepon</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="08xxxxxxxxxx / +62xxxxxxxxxx" class="mt-1 w-full rounded-xl border-stone-300 focus:border-rose-400 focus:ring-rose-300" data-testid="public-booking-phone-input">
                            <p class="mt-1 text-xs text-stone-500">Gunakan nomor WhatsApp aktif!</p>
                            @error('phone') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700">Email (opsional)</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="mt-1 w-full rounded-xl border-stone-300 focus:border-rose-400 focus:ring-rose-300">
                            @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                      @php
                          $servicesCatalog = $services->map(function ($service) {
                              return [
                                  'id' => $service->id,
                                  'name' => $service->name,
                                  'price' => (float) $service->price,
                                  'duration' => (int) $service->duration,
                              ];
                          })->values();

                          $oldServiceRows = old('services', []);

                          if (!is_array($oldServiceRows) || count($oldServiceRows) === 0) {
                              $oldServiceRows = old('service_id')
                                  ? [[
                                      'service_id' => (int) old('service_id'),
                                      'people_count' => max(1, (int) old('people_count', 1)),
                                  ]]
                                  : [[
                                      'service_id' => '',
                                      'people_count' => 1,
                                  ]];
                          }
                      @endphp

                      <div>
                          <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                              <div>
                                  <label class="block text-sm font-medium text-stone-700">Layanan dan Jumlah Orang</label>
                                  <p class="mt-1 text-xs text-stone-500">Tambahkan layanan lain bila diperlukan.</p>
                              </div>

                              <button
                                  type="button"
                                  id="add-public-service-row"
                                  class="inline-flex items-center justify-center self-start rounded-lg bg-rose-100 px-3 py-2 text-sm font-medium text-rose-700 hover:bg-rose-200"
                              >
                                  + Layanan
                              </button>
                          </div>

                          <div id="public-service-rows" class="mt-3 space-y-3"></div>

                          @error('services')
                              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                          @enderror

                          @error('services.*.service_id')
                              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                          @enderror

                          @error('services.*.people_count')
                              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                          @enderror

                          <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                              <div class="rounded-xl border border-stone-200 bg-stone-50 p-4">
                                  <p class="text-xs text-stone-500">Total orang</p>
                                  <p id="public-total-people" class="mt-1 text-lg font-semibold text-stone-900">0</p>
                              </div>

                              <div class="rounded-xl border border-stone-200 bg-stone-50 p-4">
                                  <p class="text-xs text-stone-500">Perkiraan selesai</p>
                                  <p id="public-estimated-end" class="mt-1 text-lg font-semibold text-stone-900">-</p>
                              </div>
                          </div>
                      </div>


                    <div>
                        <label class="block text-sm font-medium text-stone-700">Jenis Layanan</label>
                        @php
                            $studioNama = trim((string) ($tenant?->studio_name ?? ''));
                            $studioAddress = trim((string) ($tenant?->studio_location ?? ''));
                            $studioMap = trim((string) ($tenant?->studio_maps_link ?? ''));
                            $hasStudioOption = $studioAddress !== '' || $studioMap !== '';
                            $studioOptionLabel = $studioNama !== '' ? $studioNama : 'Studio Kami';
                            $selectedLocationMode = old('service_location', 'home_service');
                            if (! $hasStudioOption && $selectedLocationMode === 'studio') {
                                $selectedLocationMode = 'home_service';
                            }
                        @endphp
                        <select id="service_location" name="service_location" required class="mt-1 w-full rounded-xl border-stone-300 focus:border-rose-400 focus:ring-rose-300">
                            <option value="home_service" @selected($selectedLocationMode === 'home_service')>Home Service</option>
                            @if($hasStudioOption)
                                <option value="studio" @selected($selectedLocationMode === 'studio')>{{ $studioOptionLabel }}</option>
                            @endif
                        </select>
                        @error('service_location') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-stone-700">Tanggal</label>
                            <input type="date" name="booking_date" value="{{ old('booking_date') }}" required class="mt-1 w-full rounded-xl border-stone-300 focus:border-rose-400 focus:ring-rose-300" data-testid="public-booking-date-input">
                            @error('booking_date') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700">Waktu</label>
                            <input type="time" name="booking_time" value="{{ old('booking_time') }}" required class="mt-1 w-full rounded-xl border-stone-300 focus:border-rose-400 focus:ring-rose-300" data-testid="public-booking-time-input">
                            @error('booking_time') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div id="home-location-wrapper">
                        <div class="flex items-center gap-2">
                            <label class="block text-sm font-medium text-stone-700">Lokasi</label>
                            <button
                                type="button"
                                id="location-help-trigger"
                                class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-rose-100 text-rose-700 hover:bg-rose-200"
                                aria-label="Cara copy short link Google Maps"
                            >
                                i
                            </button>
                        </div>
                        <input id="location_input" type="text" name="location" value="{{ old('location') }}" placeholder="Alamat atau short Google Maps link (contoh: https://maps.app.goo.gl/...)" class="mt-1 w-full rounded-xl border-stone-300 focus:border-rose-400 focus:ring-rose-300">
                        @error('location') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div id="transport-fee-wrapper">
                        <label class="block text-sm font-medium text-stone-700">Biaya Transportasi (Opsional)</label>
                        <input
                            id="transport_fee_input"
                            type="number"
                            name="transport_fee"
                            min="0"
                            step="1000"
                            value="{{ old('transport_fee', (int) round((float) ($defaultTransportFee ?? 0))) }}"
                            class="mt-1 w-full rounded-xl border-stone-300 focus:border-rose-400 focus:ring-rose-300"
                        >
                        <p class="mt-1 text-xs text-stone-500">Isi 0 jika tidak ada biaya tambahan.</p>
                        @error('transport_fee') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    @if($hasStudioOption)
                        <div id="studio-location-wrapper" class="hidden rounded-xl border border-rose-100 bg-rose-50/60 p-4">
                            <p class="text-sm font-medium text-stone-800">{{ $studioOptionLabel }}</p>
                            @if($studioNama !== '' && $studioNama !== $studioOptionLabel)
                                <p class="mt-1 text-sm text-stone-700">{{ $studioNama }}</p>
                            @endif
                            @if($studioAddress !== '')
                                <p class="text-sm text-stone-700">{{ $studioAddress }}</p>
                            @endif
                            @if($studioMap !== '')
                                <a href="{{ $studioMap }}" target="_blank" rel="noopener noreferrer" class="inline-flex mt-2 px-3 py-1.5 rounded-lg bg-rose-100 text-rose-700 hover:bg-rose-200 text-sm">
                                    Buka Peta Studio
                                </a>
                            @endif
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-stone-700">Catatan</label>
                        <textarea name="notes" rows="3" class="mt-1 w-full rounded-xl border-stone-300 focus:border-rose-400 focus:ring-rose-300">{{ old('notes') }}</textarea>
                        @error('notes') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="rounded-xl border border-stone-200 bg-stone-50 p-4">
                        <p class="text-sm font-semibold text-stone-800">
                            {{ $termsTitle }}
                        </p>
                        <p class="mt-1 text-sm text-stone-600">
                            Silakan baca ketentuan sebelum mengirim booking.
                            <button type="button" id="terms-modal-trigger" class="font-semibold text-rose-600 hover:text-rose-700 underline underline-offset-2">
                                Lihat T&C
                            </button>
                        </p>
                        <label class="mt-3 inline-flex items-start gap-2 text-sm text-stone-700">
                            <input
                                type="checkbox"
                                name="terms_accepted"
                                value="1"
                                required
                                @checked(old('terms_accepted'))
                                class="mt-0.5 rounded border-stone-300 text-rose-500 focus:ring-rose-300"
                            >
                            <span>Saya sudah membaca dan menyetujui Syarat & Ketentuan booking di atas.</span>
                        </label>
                        @error('terms_accepted') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl bg-rose-500 text-white font-semibold hover:bg-rose-600 transition min-h-[44px]" data-testid="public-booking-submit-button">
                        Kirim Booking
                    </button>
                </form>
            </div>
        </div>
    </main>

    <div id="terms-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40" data-terms-close></div>
        <div class="relative mx-auto mt-16 w-[92%] max-w-xl rounded-2xl border border-rose-100 bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-3">
                <h3 class="text-lg font-semibold text-stone-900">{{ $termsTitle }}</h3>
                <button type="button" class="rounded-lg px-2 py-1 text-stone-500 hover:bg-stone-100" data-terms-close>x</button>
            </div>
            <div class="mt-4 max-h-[60vh] overflow-y-auto whitespace-pre-line text-sm text-stone-700">
                {{ $termsContent !== '' ? $termsContent : 'Dengan melanjutkan, Anda menyetujui kebijakan booking dari MUA terkait DP, penjadwalan ulang, dan pembatalan.' }}
            </div>
            <div class="mt-5 flex justify-end">
                <button type="button" class="rounded-xl bg-rose-500 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-600" data-terms-close>
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <div id="location-help-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40" data-location-help-close></div>
        <div class="relative mx-auto mt-16 w-[92%] max-w-md rounded-2xl border border-rose-100 bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-3">
                <h3 class="text-lg font-semibold text-stone-900">Cara Copy Short Link Google Maps</h3>
                <button type="button" class="rounded-lg px-2 py-1 text-stone-500 hover:bg-stone-100" data-location-help-close>x</button>
            </div>

            <ol class="mt-4 list-decimal pl-5 space-y-2 text-sm text-stone-700">
                <li>Buka aplikasi Google Maps di HP.</li>
                <li>Cari lokasi layanan, lalu pastikan titiknya sudah tepat.</li>
                <li>Tap tombol <strong>Share / Bagikan</strong>.</li>
                <li>Pilih <strong>Copy link / Salin link</strong>.</li>
                <li>Paste link tersebut ke kolom <strong>Lokasi</strong> di form ini.</li>
            </ol>

            <p class="mt-4 text-xs text-stone-500">
                Contoh short link: <span class="font-medium">https://maps.app.goo.gl/xxxxxx</span>
            </p>

            <div class="mt-5 flex justify-end">
                <button type="button" class="rounded-xl bg-rose-500 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-600" data-location-help-close>
                    Mengerti
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const trigger = document.getElementById('location-help-trigger');
            const modal = document.getElementById('location-help-modal');
            const termsTrigger = document.getElementById('terms-modal-trigger');
            const termsModal = document.getElementById('terms-modal');
            const serviceLokasi = document.getElementById('service_location');
            const homeLokasiWrapper = document.getElementById('home-location-wrapper');
            const studioLokasiWrapper = document.getElementById('studio-location-wrapper');
            const locationInput = document.getElementById('location_input');
            const transportFeeWrapper = document.getElementById('transport-fee-wrapper');
            const transportFeeInput = document.getElementById('transport_fee_input');
            const defaultTransportFee = String(@json((int) round((float) ($defaultTransportFee ?? 0))));
            if (!trigger || !modal) {
                // continue for location switcher even if modal fails
            }

            const close = () => modal?.classList.add('hidden');
            const open = () => modal?.classList.remove('hidden');
            const closeTerms = () => termsModal?.classList.add('hidden');
            const openTerms = () => termsModal?.classList.remove('hidden');

            trigger?.addEventListener('click', open);
            termsTrigger?.addEventListener('click', openTerms);
            modal?.querySelectorAll('[data-location-help-close]').forEach((element) => {
                element.addEventListener('click', close);
            });
            termsModal?.querySelectorAll('[data-terms-close]').forEach((element) => {
                element.addEventListener('click', closeTerms);
            });
            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    close();
                    closeTerms();
                }
            });

            const syncLokasiMode = () => {
                const mode = serviceLokasi?.value || 'home_service';
                if (mode === 'studio') {
                    homeLokasiWrapper?.classList.add('hidden');
                    studioLokasiWrapper?.classList.remove('hidden');
                    transportFeeWrapper?.classList.add('hidden');
                    locationInput?.removeAttribute('required');
                    locationInput?.setAttribute('disabled', 'disabled');
                    transportFeeInput?.setAttribute('disabled', 'disabled');
                    if (transportFeeInput) {
                        transportFeeInput.value = '0';
                    }
                } else {
                    homeLokasiWrapper?.classList.remove('hidden');
                    studioLokasiWrapper?.classList.add('hidden');
                    transportFeeWrapper?.classList.remove('hidden');
                    locationInput?.setAttribute('required', 'required');
                    locationInput?.removeAttribute('disabled');
                    transportFeeInput?.removeAttribute('disabled');
                    if (transportFeeInput && (transportFeeInput.value === '' || transportFeeInput.value === '0')) {
                        transportFeeInput.value = defaultTransportFee;
                    }
                }
            };

            serviceLokasi?.addEventListener('change', syncLokasiMode);
            syncLokasiMode();
        });
    </script>

<script>
    const publicMultiServiceInit = true;

    document.addEventListener('DOMContentLoaded', function () {
        const rowsContainer = document.getElementById('public-service-rows');
        const addButton = document.getElementById('add-public-service-row');
        const totalPeopleOutput = document.getElementById('public-total-people');
        const endOutput = document.getElementById('public-estimated-end');

        if (!rowsContainer) {
            console.error('Public Booking: #public-service-rows tidak ditemukan.');
            return;
        }

        const services = @json($servicesCatalog);
        const oldRows = @json($oldServiceRows);

        let rowIndex = 0;

        function formatRupiah(value) {
            return new Intl.NumberFormat('id-ID').format(value);
        }

        function buildOptions(selectedId) {
            let html = '<option value="">Pilih layanan</option>';

            services.forEach(function (service) {
                const selected =
                    String(service.id) === String(selectedId)
                        ? ' selected'
                        : '';

                html += `
                    <option
                        value="${service.id}"
                        data-duration="${service.duration}"
                        ${selected}
                    >
                        ${service.name} - Rp ${formatRupiah(service.price)}
                        (${service.duration} menit)
                    </option>
                `;
            });

            return html;
        }

        function updatePreview() {
            let totalPeople = 0;
            let totalDuration = 0;

            rowsContainer.querySelectorAll('[data-service-row]').forEach(function (row) {
                const serviceSelect = row.querySelector('.public-service-select');
                const peopleInput = row.querySelector('.public-people-count');

                if (!serviceSelect || !serviceSelect.value) {
                    return;
                }

                const option = serviceSelect.options[serviceSelect.selectedIndex];

                const people = Math.max(
                    1,
                    parseInt(peopleInput?.value || '1', 10)
                );

                const duration = Math.max(
                    0,
                    parseInt(option?.dataset?.duration || '0', 10)
                );

                totalPeople += people;
                totalDuration += duration * people;
            });

            if (totalPeopleOutput) {
                totalPeopleOutput.textContent = String(totalPeople);
            }

            const timeInput = document.querySelector('[name="booking_time"]');
            const timeValue = timeInput ? timeInput.value : '';

            if (!timeValue || totalDuration <= 0) {
                if (endOutput) {
                    endOutput.textContent = '-';
                }
                return;
            }

            const parts = timeValue.split(':').map(Number);

            if (parts.length < 2 || Number.isNaN(parts[0]) || Number.isNaN(parts[1])) {
                if (endOutput) {
                    endOutput.textContent = '-';
                }
                return;
            }

            const start = new Date(2000, 0, 1, parts[0], parts[1]);
            start.setMinutes(start.getMinutes() + totalDuration);

            if (endOutput) {
                endOutput.textContent =
                    String(start.getHours()).padStart(2, '0') +
                    ':' +
                    String(start.getMinutes()).padStart(2, '0');
            }
        }

        function addRow(serviceId = '', peopleCount = 1) {
            const row = document.createElement('div');

            row.setAttribute('data-service-row', '');
            row.className =
                'grid grid-cols-1 sm:grid-cols-12 gap-3 rounded-xl border border-rose-100 bg-rose-50/40 p-3';

            row.innerHTML = `
                <div class="min-w-0 sm:col-span-7">
                    <label class="block text-xs font-medium text-stone-600">
                        Layanan
                    </label>

                    <select
                        name="services[${rowIndex}][service_id]"
                        class="public-service-select mt-1 block w-full max-w-full rounded-xl border-stone-300 focus:border-rose-400 focus:ring-rose-300"
                    >
                        ${buildOptions(serviceId)}
                    </select>
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-medium text-stone-600">
                        Jumlah Orang
                    </label>

                    <input
                        type="number"
                        name="services[${rowIndex}][people_count]"
                        min="1"
                        max="20"
                        value="${Math.max(1, parseInt(peopleCount || 1, 10))}"
                        class="public-people-count mt-1 block w-full rounded-xl border-stone-300 focus:border-rose-400 focus:ring-rose-300"
                    >
                </div>

                <div class="sm:col-span-2 flex items-end">
                    <button
                        type="button"
                        class="remove-public-service-row w-full rounded-xl border border-stone-300 px-3 py-2 text-sm font-medium text-stone-700 hover:bg-stone-100"
                    >
                        Hapus
                    </button>
                </div>
            `;

            rowsContainer.appendChild(row);

            rowIndex++;

            updatePreview();
        }

        addButton?.addEventListener('click', function () {
            addRow('', 1);
        });

        rowsContainer.addEventListener('change', function (event) {
            if (event.target.classList.contains('public-service-select')) {
                updatePreview();
            }
        });

        rowsContainer.addEventListener('input', function (event) {
            if (event.target.classList.contains('public-people-count')) {
                updatePreview();
            }
        });

        rowsContainer.addEventListener('click', function (event) {
            if (!event.target.classList.contains('remove-public-service-row')) {
                return;
            }

            const rows = rowsContainer.querySelectorAll('[data-service-row]');

            if (rows.length <= 1) {
                return;
            }

            event.target.closest('[data-service-row]')?.remove();

            updatePreview();
        });

        document
            .querySelector('[name="booking_time"]')
            ?.addEventListener('input', updatePreview);

        if (Array.isArray(oldRows) && oldRows.length > 0) {
            oldRows.forEach(function (row) {
                addRow(
                    row.service_id || '',
                    row.people_count || 1
                );
            });
        } else {
            addRow('', 1);
        }

        updatePreview();
    });
</script>
</body>
</html>
