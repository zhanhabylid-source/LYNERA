<x-app-layout>
    <style>[x-cloak]{display:none!important;}</style>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">Kalender Booking</h2>
            <a href="{{ route('admin.bookings.index') }}" class="px-4 py-2.5 rounded-xl bg-rose-500 text-white hover:bg-rose-600 transition min-h-[44px] flex items-center justify-center">
                Booking
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-rose-50 via-amber-50 to-white min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->has('calendar'))
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first('calendar') }}
                </div>
            @endif
            <div class="bg-white/95 border border-rose-100 rounded-2xl shadow-lg p-4 md:p-6">
                <div class="mb-4 flex flex-wrap items-center gap-2 text-sm">
                    <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700">Menunggu</span>
                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-700">Dikonfirmasi</span>
                    <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700">Selesai</span>
                    <span class="px-3 py-1 rounded-full bg-red-100 text-red-700">Dibatalkan</span>
                </div>
                <div id="booking-calendar"></div>
            </div>

            <div class="mt-6 bg-white/95 border border-rose-100 rounded-2xl shadow-lg p-4 md:p-6">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-lg font-semibold text-stone-900">Jadwal Besok ({{ \Carbon\Carbon::parse($tomorrowDate)->translatedFormat('d F Y') }})</h3>
                    <span class="text-xs rounded-full {{ $tomorrowBookings->count() > 0 ? 'bg-red-100 text-red-700' : 'bg-stone-100 text-stone-600' }} px-3 py-1 font-semibold">
                        {{ $tomorrowBookings->count() }} booking
                    </span>
                </div>

                @if($tomorrowBookings->isEmpty())
                    <div class="mt-4 rounded-xl border border-dashed border-stone-300 bg-stone-50 p-4 text-sm text-stone-500">
                        Tidak ada jadwal booking untuk besok.
                    </div>
                @else
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($tomorrowBookings as $booking)
                            @php
                                $statusLabel = match ($booking->status) {
                                    'pending' => 'Menunggu',
                                    'confirmed' => 'Dikonfirmasi',
                                    'completed' => 'Selesai',
                                    'canceled' => 'Dibatalkan',
                                    default => ucfirst((string) $booking->status),
                                };
                                $whatsAppPhone = preg_replace('/\D+/', '', (string) ($booking->customer->phone ?? ''));
                                if (is_string($whatsAppPhone) && str_starts_with($whatsAppPhone, '0')) {
                                    $whatsAppPhone = '62'.substr($whatsAppPhone, 1);
                                }
                                $whatsAppMessage = rawurlencode(sprintf(
                                    'Halo %s, ini pengingat booking layanan %s untuk besok jam %s.',
                                    (string) ($booking->customer->name ?? ''),
                                    (string) ($booking->service->name ?? ''),
                                    substr((string) $booking->booking_time, 0, 5)
                                ));
                            @endphp
                            <div class="rounded-xl border border-red-100 bg-red-50/40 p-4">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div>
                                        <p class="font-semibold text-stone-900">{{ $booking->service->name ?? '-' }}</p>
                                        <p class="text-sm text-stone-600">{{ $booking->customer->name ?? '-' }}</p>
                                    </div>
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                        {{ $booking->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $booking->status === 'completed' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $booking->status === 'canceled' ? 'bg-red-100 text-red-700' : '' }}
                                    ">
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-stone-700">
                                    {{ substr((string) $booking->booking_time, 0, 5) }} - {{ substr((string) $booking->end_time, 0, 5) }}
                                    | {{ (int) ($booking->total_people ?? 1) }} orang
                                </p>
                                <p class="mt-1 text-sm text-stone-600">Lokasi: {{ $booking->location ?: '-' }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <a href="{{ route('admin.bookings.show', $booking) }}" class="inline-flex items-center rounded-lg border border-stone-300 bg-white px-3 py-1.5 text-xs font-semibold text-stone-700 hover:bg-stone-50">
                                        Detail
                                    </a>
                                    @if($booking->status === 'pending')
                                        <form method="POST" action="{{ route('admin.bookings.confirm', $booking) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center rounded-lg border border-green-300 bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-700 hover:bg-green-100">
                                                Konfirmasi
                                            </button>
                                        </form>
                                    @endif
                                    @if(!empty($whatsAppPhone))
                                        <a
                                            href="https://wa.me/{{ $whatsAppPhone }}?text={{ $whatsAppMessage }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center rounded-lg border border-rose-300 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100"
                                        >
                                            WA
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="mt-6 bg-white/95 border border-rose-100 rounded-2xl shadow-lg p-4 md:p-6">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h3 id="monthly-booking-title" class="text-lg font-semibold text-stone-900">Daftar Booking Bulan Ini</h3>
                    <span id="monthly-booking-count" class="text-xs rounded-full bg-rose-100 px-3 py-1 font-semibold text-rose-700">0 booking</span>
                </div>
                <p class="mt-1 text-sm text-stone-500">
                    Daftar ini mengikuti bulan yang sedang tampil pada kalender di atas.
                </p>
                <div id="monthly-booking-list" class="mt-4 space-y-3"></div>
            </div>
        </div>
    </div>

    <div
        x-data="calendarBookingModal()"
        x-init="window.calendarBookingModal = $data"
        x-show="isOpen"
        x-cloak
        class="fixed inset-0 z-50"
        @keydown.escape.window="close()"
    >
        <div class="absolute inset-0 bg-black/40" @click="close()"></div>
        <div class="relative max-w-xl mx-auto mt-16 md:mt-24 bg-white rounded-2xl shadow-2xl border border-rose-100 p-6">
            <h3 class="text-xl font-semibold text-stone-800">Buat Booking</h3>
            <p class="text-sm text-stone-500 mt-1">Tanggal: <span x-text="form.booking_date"></span></p>

            <template x-if="error">
                <p class="mt-3 text-sm text-red-600" x-text="error"></p>
            </template>

            <form class="mt-5 space-y-4" @submit.prevent="submitForm()">
                <div>
                    <label class="block text-sm font-medium text-stone-700">Layanan</label>
                    <select id="calendar_service_id" x-model="form.service_id" @change="calculateEndTime()" class="mt-1 w-full rounded-lg border-stone-300 focus:border-rose-400 focus:ring-rose-300">
                        <option value="">Pilih layanan</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}" data-duration="{{ $service->duration }}">{{ $service->name }} ({{ $service->duration }} min)</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700">Pelanggan</label>
                    <select x-model="form.customer_id" class="mt-1 w-full rounded-lg border-stone-300 focus:border-rose-400 focus:ring-rose-300">
                        <option value="">Pilih pelanggan</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700">Jam Mulai</label>
                        <input type="time" x-model="form.booking_time" @input="calculateEndTime()" class="mt-1 w-full rounded-lg border-stone-300 focus:border-rose-400 focus:ring-rose-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700">Perkiraan Selesai</label>
                        <input type="text" x-model="endTimePreview" readonly class="mt-1 w-full rounded-lg border-stone-200 bg-stone-50 text-stone-600">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700">Lokasi</label>
                        <input type="text" x-model="form.location" placeholder="Alamat atau short Google Maps link" class="mt-1 w-full rounded-lg border-stone-300 focus:border-rose-400 focus:ring-rose-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700">Status</label>
                        <select x-model="form.status" class="mt-1 w-full rounded-lg border-stone-300 focus:border-rose-400 focus:ring-rose-300">
                            <option value="pending">Menunggu</option>
                            <option value="confirmed">Dikonfirmasi</option>
                            <option value="completed">Selesai</option>
                            <option value="canceled">Dibatalkan</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700">Catatan</label>
                    <textarea x-model="form.notes" rows="3" class="mt-1 w-full rounded-lg border-stone-300 focus:border-rose-400 focus:ring-rose-300"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="close()" class="px-5 py-2.5 rounded-xl border border-stone-300 text-stone-700 hover:bg-stone-50">Batal</button>
                    <button type="submit" :disabled="loading" class="px-6 py-2.5 rounded-xl bg-rose-500 text-white hover:bg-rose-600 disabled:opacity-50">
                        <span x-show="!loading">Simpan</span>
                        <span x-show="loading">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="calendar-detail-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40" data-calendar-detail-close></div>
        <div class="relative max-w-xl mx-auto mt-16 md:mt-24 bg-white rounded-2xl shadow-2xl border border-rose-100 p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-stone-500">Detail Booking</p>
                    <h3 id="calendar-detail-title" class="mt-1 text-xl font-semibold text-stone-900">-</h3>
                </div>
                <button type="button" class="rounded-lg px-2 py-1 text-stone-500 hover:bg-stone-100" data-calendar-detail-close>x</button>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-3 text-sm">
                <div class="rounded-xl bg-stone-50 p-3">
                    <p class="text-stone-500">Pelanggan</p>
                    <p id="calendar-detail-customer" class="mt-1 font-medium text-stone-800">-</p>
                </div>
                <div class="rounded-xl bg-stone-50 p-3">
                    <p class="text-stone-500">Telepon</p>
                    <p id="calendar-detail-phone" class="mt-1 font-medium text-stone-800">-</p>
                </div>
                <div class="rounded-xl bg-stone-50 p-3">
                    <p class="text-stone-500">Jumlah Orang</p>
                    <p id="calendar-detail-total-people" class="mt-1 font-medium text-stone-800">-</p>
                </div>
                <div class="rounded-xl bg-stone-50 p-3">
                    <p class="text-stone-500">Makeup / Layanan</p>
                    <p id="calendar-detail-services" class="mt-1 font-medium text-stone-800">-</p>
                </div>
                <div class="rounded-xl bg-stone-50 p-3">
                    <p class="text-stone-500">Jadwal</p>
                    <p id="calendar-detail-schedule" class="mt-1 font-medium text-stone-800">-</p>
                </div>
                <div class="rounded-xl bg-stone-50 p-3">
                    <p class="text-stone-500">Status</p>
                    <p id="calendar-detail-status" class="mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold">-</p>
                </div>
                <div class="rounded-xl bg-stone-50 p-3">
                    <p class="text-stone-500">Lokasi</p>
                    <p id="calendar-detail-location" class="mt-1 font-medium text-stone-800">-</p>
                </div>
                <div class="rounded-xl bg-stone-50 p-3">
                    <p class="text-stone-500">Catatan</p>
                    <p id="calendar-detail-notes" class="mt-1 font-medium text-stone-800">-</p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-5">
                <button type="button" class="px-5 py-2.5 rounded-xl bg-rose-500 text-white hover:bg-rose-600 transition" data-calendar-detail-close>
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('calendarBookingModal', () => ({
                isOpen: false,
                loading: false,
                error: '',
                endTimePreview: '',
                form: {
                    booking_date: '',
                    booking_time: '10:00',
                    service_id: '',
                    customer_id: '',
                    location: '',
                    status: 'pending',
                    notes: '',
                },
                open(date) {
                    this.isOpen = true;
                    this.error = '';
                    this.form.booking_date = date;
                    this.calculateEndTime();
                },
                close() {
                    this.isOpen = false;
                    this.error = '';
                },
                calculateEndTime() {
                    const serviceSelect = document.getElementById('calendar_service_id');
                    const selected = serviceSelect?.selectedOptions?.[0];
                    const duration = parseInt(selected?.dataset?.duration || '0', 10);

                    if (!this.form.booking_time || Number.isNaN(duration) || duration <= 0) {
                        this.endTimePreview = '-';
                        return;
                    }

                    const [hours, minutes] = this.form.booking_time.split(':').map(Number);
                    const date = new Date(2000, 0, 1, hours, minutes + duration);
                    const hh = String(date.getHours()).padStart(2, '0');
                    const mm = String(date.getMinutes()).padStart(2, '0');
                    this.endTimePreview = `${hh}:${mm}`;
                },
                async submitForm() {
                    this.loading = true;
                    this.error = '';
                    try {
                        const response = await fetch("{{ route('admin.calendar.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            },
                            body: JSON.stringify(this.form),
                        });

                        if (!response.ok) {
                            const payload = await response.json();
                            this.error = payload.message || 'Gagal membuat booking.';
                            return;
                        }

                        this.close();
                        localStorage.setItem('booking:lastCreatedAt', String(Date.now()));
                        window.dispatchEvent(new CustomEvent('booking-created'));
                    } finally {
                        this.loading = false;
                    }
                },
            }));
        });

        document.addEventListener('DOMContentLoaded', () => {
            const calendarEl = document.getElementById('booking-calendar');
            const tomorrowDate = @js($tomorrowDate);
            const detailModal = document.getElementById('calendar-detail-modal');
            const detailTitle = document.getElementById('calendar-detail-title');
            const detailPelanggan = document.getElementById('calendar-detail-customer');
            const detailTelepon = document.getElementById('calendar-detail-phone');
            const detailTotalPeople = document.getElementById('calendar-detail-total-people');
            const detailLayanan = document.getElementById('calendar-detail-services');
            const detailJadwal = document.getElementById('calendar-detail-schedule');
            const detailStatus = document.getElementById('calendar-detail-status');
            const detailLokasi = document.getElementById('calendar-detail-location');
            const detailCatatan = document.getElementById('calendar-detail-notes');
            const monthlyListTitle = document.getElementById('monthly-booking-title');
            const monthlyListCount = document.getElementById('monthly-booking-count');
            const monthlyListEl = document.getElementById('monthly-booking-list');
            let currentMonthDate = new Date();
            let latestEvents = [];

            const formatDateTime = (value) => {
                if (!value) {
                    return '-';
                }
                return new Intl.DateTimeFormat('id-ID', {
                    dateStyle: 'medium',
                    timeStyle: 'short',
                }).format(new Date(value));
            };

            const statusBadgeClass = (status) => {
                const map = {
                    pending: 'bg-yellow-100 text-yellow-700',
                    confirmed: 'bg-green-100 text-green-700',
                    completed: 'bg-blue-100 text-blue-700',
                    canceled: 'bg-red-100 text-red-700',
                };
                return map[status] || 'bg-stone-100 text-stone-700';
            };
            const statusLabel = (status) => {
                const map = {
                    pending: 'Menunggu',
                    confirmed: 'Dikonfirmasi',
                    completed: 'Selesai',
                    canceled: 'Dibatalkan',
                };
                return map[status] || '-';
            };

            const formatMonthYear = (value) => {
                return new Intl.DateTimeFormat('id-ID', {
                    month: 'long',
                    year: 'numeric',
                }).format(value);
            };

            const renderMonthlyBookingList = () => {
                if (!monthlyListEl || !monthlyListTitle || !monthlyListCount) {
                    return;
                }

                const targetMonth = currentMonthDate.getMonth();
                const targetYear = currentMonthDate.getFullYear();

                const monthEvents = latestEvents
                    .filter((event) => event.start instanceof Date)
                    .filter((event) => event.start.getMonth() === targetMonth && event.start.getFullYear() === targetYear)
                    .sort((a, b) => a.start.getTime() - b.start.getTime());

                monthlyListTitle.textContent = `Daftar Booking ${formatMonthYear(currentMonthDate)}`;
                monthlyListCount.textContent = `${monthEvents.length} booking`;
                monthlyListEl.innerHTML = '';

                if (monthEvents.length === 0) {
                    const emptyState = document.createElement('div');
                    emptyState.className = 'rounded-xl border border-dashed border-stone-300 bg-stone-50 p-4 text-sm text-stone-500';
                    emptyState.textContent = 'Belum ada booking pada bulan ini.';
                    monthlyListEl.appendChild(emptyState);
                    return;
                }

                monthEvents.forEach((event) => {
                    const card = document.createElement('div');
                    card.className = 'rounded-xl border border-rose-100 bg-rose-50/40 p-4';

                    const topRow = document.createElement('div');
                    topRow.className = 'flex flex-wrap items-start justify-between gap-2';

                    const left = document.createElement('div');
                    const title = document.createElement('p');
                    title.className = 'font-semibold text-stone-900';
                    title.textContent = event.extendedProps.service_name || event.title || '-';
                    const customer = document.createElement('p');
                    customer.className = 'text-sm text-stone-600';
                    customer.textContent = event.extendedProps.customer_name || '-';
                    left.appendChild(title);
                    left.appendChild(customer);

                    const badge = document.createElement('span');
                    badge.className = `inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${statusBadgeClass(event.extendedProps.status)}`;
                    badge.textContent = statusLabel(event.extendedProps.status);

                    topRow.appendChild(left);
                    topRow.appendChild(badge);

                    const detail = document.createElement('p');
                    detail.className = 'mt-2 text-sm text-stone-700';
                    detail.textContent = `${formatDateTime(event.start)} - ${formatDateTime(event.end)} | ${event.extendedProps.total_people || 1} orang`;

                    const location = document.createElement('p');
                    location.className = 'mt-1 text-sm text-stone-600';
                    location.textContent = `Lokasi: ${event.extendedProps.location || '-'}`;

                    card.appendChild(topRow);
                    card.appendChild(detail);
                    card.appendChild(location);
                    monthlyListEl.appendChild(card);
                });
            };

            const isGoogleMapsLink = (value) => {
                try {
                    const url = new URL(value);
                    const host = url.hostname.toLowerCase();
                    return ['maps.app.goo.gl', 'goo.gl', 'google.com', 'www.google.com', 'maps.google.com', 'g.co']
                        .some((allowed) => host === allowed || host.endsWith(`.${allowed}`));
                } catch (error) {
                    return false;
                }
            };

            const setLokasiContent = (element, value) => {
                element.innerHTML = '';
                if (!value) {
                    element.textContent = '-';
                    return;
                }
                if (isGoogleMapsLink(value)) {
                    const link = document.createElement('a');
                    link.href = value;
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                    link.className = 'inline-flex items-center px-3 py-1.5 rounded-lg bg-rose-100 text-rose-700 hover:bg-rose-200';
                    link.textContent = 'Buka Google Maps';
                    element.appendChild(link);
                    return;
                }
                element.textContent = value;
            };

            const closeDetailModal = () => {
                detailModal.classList.add('hidden');
            };

            const openDetailModal = (event) => {
                detailTitle.textContent = event.extendedProps.service_name || event.title || '-';
                detailPelanggan.textContent = event.extendedProps.customer_name || '-';
                detailTelepon.textContent = event.extendedProps.customer_phone || '-';
                detailTotalPeople.textContent = `${event.extendedProps.total_people || 1} orang`;
                detailLayanan.textContent = event.extendedProps.services_summary || event.extendedProps.service_name || '-';
                detailJadwal.textContent = `${formatDateTime(event.start)} - ${formatDateTime(event.end)}`;
                detailStatus.textContent = (event.extendedProps.status || '-').toUpperCase();
                detailStatus.className = `mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${statusBadgeClass(event.extendedProps.status)}`;
                setLokasiContent(detailLokasi, event.extendedProps.location || '');
                detailCatatan.textContent = event.extendedProps.notes || '-';
                detailModal.classList.remove('hidden');
            };

            detailModal.querySelectorAll('[data-calendar-detail-close]').forEach((button) => {
                button.addEventListener('click', closeDetailModal);
            });

            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeDetailModal();
                }
            });

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 'auto',
                editable: true,
                eventStartEditable: true,
                eventDurationEditable: true,
                selectable: true,
                nowIndicator: true,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay',
                },
                events: '/admin/calendar/events',
                datesSet(info) {
                    currentMonthDate = new Date(info.view.currentStart);
                    renderMonthlyBookingList();
                },
                eventsSet(events) {
                    latestEvents = events.slice();
                    renderMonthlyBookingList();
                },
                eventDidMount(info) {
                    const eventDate = info.event.startStr?.slice(0, 10) || '';
                    if (eventDate === tomorrowDate) {
                        info.el.classList.add('ring-2', 'ring-red-300');
                    }
                },
                dayCellDidMount(info) {
                    if (info.dateStr === tomorrowDate) {
                        info.el.classList.add('bg-amber-50');
                        const marker = document.createElement('span');
                        marker.className = 'absolute top-1 right-1 inline-flex h-2.5 w-2.5 rounded-full bg-red-500';
                        marker.setAttribute('title', 'Besok');
                        info.el.style.position = 'relative';
                        info.el.appendChild(marker);
                    }
                },
                eventClick(info) {
                    openDetailModal(info.event);
                },
                dateClick(info) {
                    window.calendarBookingModal?.open(info.dateStr);
                },
                async eventResize(info) {
                    const success = await syncReschedule(info.event);
                    if (!success) {
                        info.revert();
                    }
                },
                async eventDrop(info) {
                    const success = await syncReschedule(info.event);
                    if (!success) {
                        info.revert();
                    }
                },
            });

            async function syncReschedule(event) {
                const toDateString = (dateObj) => {
                    if (!(dateObj instanceof Date)) {
                        return '';
                    }
                    const yyyy = dateObj.getFullYear();
                    const mm = String(dateObj.getMonth() + 1).padStart(2, '0');
                    const dd = String(dateObj.getDate()).padStart(2, '0');

                    return `${yyyy}-${mm}-${dd}`;
                };

                const toTimeString = (dateObj) => {
                    if (!(dateObj instanceof Date)) {
                        return null;
                    }
                    const hh = String(dateObj.getHours()).padStart(2, '0');
                    const mm = String(dateObj.getMinutes()).padStart(2, '0');
                    const ss = String(dateObj.getSeconds()).padStart(2, '0');

                    return `${hh}:${mm}:${ss}`;
                };

                const date = toDateString(event.start);
                const startTime = toTimeString(event.start);
                const endTime = toTimeString(event.end);

                try {
                    const response = await fetch(`/admin/bookings/${event.id}/reschedule`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({
                            date,
                            start_time: startTime,
                            end_time: endTime,
                        }),
                    });

                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok || payload.success === false) {
                        alert(payload.message || 'Jadwal bentrok.');
                        return false;
                    }

                    return true;
                } catch (error) {
                    alert('Terjadi gangguan jaringan saat mengubah jadwal booking.');
                    return false;
                }
            }

            calendar.render();

            window.addEventListener('booking-created', () => {
                calendar.refetchEvents();
            });
        });
    </script>
</x-app-layout>


