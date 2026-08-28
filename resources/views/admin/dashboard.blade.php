<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-stone-800 leading-tight">
            Dasbor Admin
        </h2>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-rose-50 via-amber-50 to-white min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(!empty($expiryWarning))
                <div
                    class="flex flex-col gap-3 rounded-2xl border p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between {{ $expiryWarning['type'] === 'expired' ? 'border-rose-200 bg-rose-50 text-rose-800' : 'border-amber-200 bg-amber-50 text-amber-800' }}"
                    data-testid="subscription-expiry-banner"
                >
                    <div class="flex items-start gap-3">
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $expiryWarning['type'] === 'expired' ? 'bg-rose-600' : 'bg-amber-500' }} text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" /></svg>
                        </span>
                        <div>
                            @if($expiryWarning['type'] === 'expired')
                                <p class="text-sm font-semibold">Langganan Anda telah berakhir pada {{ $expiryWarning['date']->translatedFormat('d F Y') }}.</p>
                                <p class="text-sm">Perpanjang sekarang untuk mengaktifkan kembali seluruh fitur bisnis Anda.</p>
                            @else
                                <p class="text-sm font-semibold">Langganan Anda akan berakhir {{ $expiryWarning['days'] <= 0 ? 'hari ini' : 'dalam '.$expiryWarning['days'].' hari' }} ({{ $expiryWarning['date']->translatedFormat('d F Y') }}).</p>
                                <p class="text-sm">Perpanjang lebih awal agar layanan tetap berjalan tanpa gangguan.</p>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('billing.index') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold text-white {{ $expiryWarning['type'] === 'expired' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-amber-500 hover:bg-amber-600' }}" data-testid="subscription-expiry-renew-button">
                        Perpanjang Sekarang
                    </a>
                </div>
            @endif

            @if($hasPlanActivationNotice ?? false)
                <div class="p-4 rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-800 shadow-sm">
                    Paket Anda saat ini telah diaktifkan ({{ strtoupper($planActivationNoticePlan ?? $plan) }}), selamat menggunakan layanan kami.
                </div>
            @endif

            <div class="rounded-[2rem] border border-rose-100 bg-white/90 p-6 shadow-xl shadow-rose-100">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-sm font-semibold uppercase tracking-[0.32em] text-rose-600">Selamat datang kembali</p>
                        <h1 class="mt-3 text-4xl font-semibold tracking-tight text-stone-900 sm:text-5xl">Halo, {{ Auth::user()->name }} 👋</h1>
                        <p class="mt-4 max-w-2xl text-base leading-7 text-stone-600">Sistem LYNERA siap membantu Anda menerima booking, mengelola layanan, dan mengikuti jadwal dengan lebih mudah setiap hari.</p>
                    </div>
                    <div class="inline-flex rounded-3xl border border-amber-100 bg-amber-50 px-5 py-4 text-sm text-stone-700 shadow-sm">
                        <div>
                            <p class="font-semibold text-stone-900">Paket saat ini</p>
                            <p class="mt-1 uppercase tracking-[0.24em] text-amber-700">{{ strtoupper($planDetail['name'] ?? $plan) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $alertClass = 'bg-yellow-50 border-yellow-200 text-yellow-800';
                $alertText = 'Paket '.strtoupper($plan).' aktif';
                if ($plan !== 'free') {
                    $alertClass = 'bg-emerald-50 border-emerald-200 text-emerald-800';
                    $alertText = 'Paket '.strtoupper($plan).' aktif';
                }
            @endphp

            <div class="p-5 rounded-2xl border {{ $alertClass }} shadow-md">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="space-y-3">
                        <p class="text-sm font-semibold text-stone-900">{{ $alertText }}</p>
                        <p class="text-sm text-stone-600">{{ $planDetail['booking_limit_label'] ?? '-' }}</p>
                        <p class="text-sm text-stone-600">
                            Total booking terpakai: {{ $bookingUsage['bookings_count'] }}
                            @if($bookingUsage['is_unlimited'])
                                booking
                            @else
                                dari {{ $bookingUsage['limit'] }} booking (sisa {{ $bookingUsage['remaining'] }})
                            @endif
                        </p>
                        @if(! $bookingUsage['is_unlimited'])
                            <div class="mt-2 h-2 w-full max-w-sm rounded-full bg-white/70">
                                <div class="h-2 rounded-full bg-rose-500" style="width: {{ $bookingUsage['percent_used'] }}%;"></div>
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('billing.index') }}" class="inline-flex items-center rounded-3xl bg-stone-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-stone-200 transition hover:bg-black">Upgrade Paket</a>
                        <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center rounded-3xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-700 transition hover:bg-stone-50">Lihat Booking</a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-2xl shadow border border-rose-100">
                    <p class="text-sm text-stone-500">Total Booking</p>
                    <p class="mt-2 text-3xl font-bold text-stone-900">{{ $totalBookings }}</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow border border-rose-100">
                    <p class="text-sm text-stone-500">Total Pelanggan</p>
                    <p class="mt-2 text-3xl font-bold text-stone-900">{{ $totalCustomers }}</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow border border-rose-100" data-testid="dashboard-revenue-card">
                    <p class="text-sm text-stone-500">Total Uang Diterima</p>
                    <p class="mt-2 text-3xl font-bold text-stone-900">
                        Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-xs text-stone-500">Termasuk DP + pelunasan</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow border border-rose-100">
                <h3 class="text-lg font-semibold text-stone-900">Aksi Cepat</h3>
                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="{{ route('admin.services.index') }}" class="px-5 py-2.5 bg-rose-500 text-white rounded-xl hover:bg-rose-600 transition min-h-[44px] flex items-center">
                        Layanan
                    </a>
                    <a href="{{ route('admin.customers.index') }}" class="px-5 py-2.5 bg-stone-700 text-white rounded-xl hover:bg-stone-800 transition min-h-[44px] flex items-center">
                        Pelanggan
                    </a>
                    <a href="{{ route('admin.bookings.index') }}" class="px-5 py-2.5 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition min-h-[44px] flex items-center">
                        Booking
                    </a>
                    <a href="{{ route('admin.calendar.index') }}" class="px-5 py-2.5 bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition min-h-[44px] flex items-center">
                        Kalender
                    </a>
                    <a href="{{ route('admin.payments.index') }}" class="px-5 py-2.5 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition min-h-[44px] flex items-center">
                        Bayar
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="px-5 py-2.5 bg-stone-700 text-white rounded-xl hover:bg-stone-800 transition min-h-[44px] flex items-center">
                        Laporan
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow border border-rose-100">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-lg font-semibold text-stone-900">Widget Kalender</h3>
                        <span class="text-xs px-3 py-1 rounded-full bg-rose-100 text-rose-700">
                            Update saat ada perubahan booking
                        </span>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                        <span class="px-2.5 py-1 rounded-full bg-yellow-100 text-yellow-700">Menunggu</span>
                        <span class="px-2.5 py-1 rounded-full bg-green-100 text-green-700">Dikonfirmasi</span>
                    </div>
                    <div class="mt-4" id="dashboard-mini-calendar"></div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow border border-rose-100">
                    <h3 class="text-lg font-semibold text-stone-900">Booking Mendatang</h3>
                    <div class="mt-4 space-y-3">
                        @forelse($upcomingBookings as $booking)
                            <div class="p-3 rounded-xl border border-stone-200 bg-stone-50">
                                <p class="font-medium text-stone-800">{{ $booking->service->name }} - {{ $booking->customer->name }}</p>
                                <p class="text-sm text-stone-600">
                                    {{ $booking->booking_date?->format('d M Y') }} pukul {{ substr((string) $booking->booking_time, 0, 5) }}
                                </p>
                                <p class="text-sm text-stone-600 mt-1">
                                    {{ (int) ($booking->total_people ?? 1) }} orang |
                                    @php
                                        $location = (string) ($booking->location ?? '');
                                        $isMapLink = preg_match('/^https?:\/\//i', $location) === 1;
                                    @endphp
                                    @if($location === '')
                                        Lokasi belum diisi
                                    @elseif($isMapLink)
                                        <a href="{{ $location }}" target="_blank" rel="noopener noreferrer" class="text-rose-600 font-medium hover:text-rose-700">
                                            Buka Maps
                                        </a>
                                    @else
                                        {{ $location }}
                                    @endif
                                </p>
                            </div>
                        @empty
                            <div class="p-4 rounded-xl border border-dashed border-stone-300 bg-stone-50">
                                <p class="text-sm text-stone-600">Belum ada booking.</p>
                                <a href="{{ route('admin.bookings.create') }}" class="inline-flex mt-2 text-sm font-medium text-rose-600 hover:text-rose-700">
                                    Buat booking pertama Anda
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            @if($servicesCount === 0)
                <div class="rounded-[2rem] border border-dashed border-rose-200 bg-rose-50/80 p-8 text-center">
                    <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl border border-rose-100 bg-white mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-stone-900">Mulai dengan menambahkan layanan</h3>
                    <p class="mt-2 max-w-xl mx-auto text-sm text-stone-600">Daftarkan layanan makeup Anda untuk mulai menerima booking dari klien dan mengelola harga layanan dengan mudah.</p>
                    <a href="{{ route('admin.services.create') }}" class="mt-6 inline-flex items-center rounded-2xl bg-rose-500 px-6 py-3 text-sm font-semibold text-white shadow-md hover:bg-rose-600 transition">
                        Buat Layanan Pertama
                    </a>
                </div>
            @endif
        </div>
    </div>

    <div id="booking-detail-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40" data-modal-close></div>
        <div class="relative mx-auto mt-16 w-[92%] max-w-lg rounded-2xl border border-rose-100 bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-stone-500">Detail Booking</p>
                    <h3 id="modal-title" class="mt-1 text-xl font-semibold text-stone-900">-</h3>
                </div>
                <button type="button" class="rounded-lg px-2 py-1 text-stone-500 hover:bg-stone-100" data-modal-close>x</button>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-3 text-sm">
                <div class="rounded-xl bg-stone-50 p-3">
                    <p class="text-stone-500">Pelanggan</p>
                    <p id="modal-customer" class="mt-1 font-medium text-stone-800">-</p>
                </div>
                <div class="rounded-xl bg-stone-50 p-3">
                    <p class="text-stone-500">Telepon</p>
                    <p id="modal-phone" class="mt-1 font-medium text-stone-800">-</p>
                </div>
                <div class="rounded-xl bg-stone-50 p-3">
                    <p class="text-stone-500">Jumlah Orang</p>
                    <p id="modal-total-people" class="mt-1 font-medium text-stone-800">-</p>
                </div>
                <div class="rounded-xl bg-stone-50 p-3">
                    <p class="text-stone-500">Makeup / Layanan</p>
                    <p id="modal-services" class="mt-1 font-medium text-stone-800">-</p>
                </div>
                <div class="rounded-xl bg-stone-50 p-3">
                    <p class="text-stone-500">Jadwal</p>
                    <p id="modal-schedule" class="mt-1 font-medium text-stone-800">-</p>
                </div>
                <div class="rounded-xl bg-stone-50 p-3">
                    <p class="text-stone-500">Status</p>
                    <p id="modal-status" class="mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold">-</p>
                </div>
                <div class="rounded-xl bg-stone-50 p-3">
                    <p class="text-stone-500">Lokasi</p>
                    <p id="modal-location" class="mt-1 font-medium text-stone-800">-</p>
                </div>
                <div class="rounded-xl bg-stone-50 p-3">
                    <p class="text-stone-500">Catatan</p>
                    <p id="modal-notes" class="mt-1 font-medium text-stone-800">-</p>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" class="rounded-xl bg-rose-500 px-5 py-2.5 text-white hover:bg-rose-600 transition" data-modal-close>
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const el = document.getElementById('dashboard-mini-calendar');
            if (!el) {
                return;
            }

            const showToast = (message, variant = 'success') => {
                const toast = document.createElement('div');
                const classes = variant === 'warning'
                    ? 'bg-amber-100 border-amber-200 text-amber-800'
                    : 'bg-emerald-100 border-emerald-200 text-emerald-800';
                toast.className = `fixed bottom-5 right-5 z-50 px-4 py-3 rounded-xl border shadow-md text-sm ${classes}`;
                toast.textContent = message;
                document.body.appendChild(toast);
                setTimeout(() => {
                    toast.classList.add('opacity-0', 'transition');
                    setTimeout(() => toast.remove(), 250);
                }, 2500);
            };

            const detailModal = document.getElementById('booking-detail-modal');
            const modalTitle = document.getElementById('modal-title');
            const modalPelanggan = document.getElementById('modal-customer');
            const modalTelepon = document.getElementById('modal-phone');
            const modalTotalPeople = document.getElementById('modal-total-people');
            const modalLayanan = document.getElementById('modal-services');
            const modalJadwal = document.getElementById('modal-schedule');
            const modalStatus = document.getElementById('modal-status');
            const modalLokasi = document.getElementById('modal-location');
            const modalCatatan = document.getElementById('modal-notes');

            const formatDateTime = (value) => {
                if (!value) {
                    return '-';
                }
                const date = new Date(value);
                return new Intl.DateTimeFormat('id-ID', {
                    dateStyle: 'medium',
                    timeStyle: 'short',
                }).format(date);
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

            const openDetailModal = (event) => {
                modalTitle.textContent = event.extendedProps.service_name || event.title || '-';
                modalPelanggan.textContent = event.extendedProps.customer_name || '-';
                modalTelepon.textContent = event.extendedProps.customer_phone || '-';
                modalTotalPeople.textContent = `${event.extendedProps.total_people || 1} orang`;
                modalLayanan.textContent = event.extendedProps.services_summary || event.extendedProps.service_name || '-';
                modalJadwal.textContent = `${formatDateTime(event.start)} - ${formatDateTime(event.end)}`;
                modalStatus.textContent = (event.extendedProps.status || '-').toUpperCase();
                modalStatus.className = `mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${statusBadgeClass(event.extendedProps.status)}`;
                setLokasiContent(modalLokasi, event.extendedProps.location || '');
                modalCatatan.textContent = event.extendedProps.notes || '-';
                detailModal.classList.remove('hidden');
            };

            const closeDetailModal = () => {
                detailModal.classList.add('hidden');
            };

            detailModal.querySelectorAll('[data-modal-close]').forEach((button) => {
                button.addEventListener('click', closeDetailModal);
            });
            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeDetailModal();
                }
            });

            const knownEventIds = new Set();

            const miniCalendar = new FullCalendar.Calendar(el, {
                initialView: 'dayGridMonth',
                height: 430,
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: '',
                },
                events: '/admin/calendar/events',
                eventDidMount(info) {
                    info.el.title = `${info.event.title} (${info.event.extendedProps.status || 'booking'})`;
                },
                eventClick(info) {
                    openDetailModal(info.event);
                },
                eventsSet(events) {
                    let hasNewEvent = false;
                    events.forEach((event) => {
                        const eventId = String(event.id ?? event.startStr + event.title);
                        if (!knownEventIds.has(eventId)) {
                            if (knownEventIds.size > 0) {
                                hasNewEvent = true;
                            }
                            knownEventIds.add(eventId);
                        }
                    });

                    if (hasNewEvent) {
                        showToast('Booking baru muncul di widget kalender.');
                    }
                },
            });

            miniCalendar.render();

            window.addEventListener('focus', () => {
                miniCalendar.refetchEvents();
            });

            window.addEventListener('storage', (event) => {
                if (event.key === 'booking:lastCreatedAt') {
                    miniCalendar.refetchEvents();
                    showToast('Booking baru terdeteksi, kalender diperbarui.');
                }
            });
        });
    </script>
</x-app-layout>


