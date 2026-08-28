<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Terima Kasih</title>
    @include('partials.vite-assets')
</head>
<body class="font-sans bg-gradient-to-b from-rose-50 via-amber-50 to-white min-h-screen">
    <main class="py-12 px-[18px] sm:px-6">
        <div class="max-w-xl mx-auto">
            <div class="bg-white rounded-2xl border border-rose-100 shadow-xl p-7 text-center">
                <h1 class="text-2xl font-semibold text-stone-900">Terima Kasih</h1>
                <p class="mt-3 text-stone-700">
                    Bookingan anda sedang kami proses, setelah penyesuaian jadwal kami akan menghubungi anda melalui No WhatsApp yang anda cantumkan pada form ini.
                </p>
                @if($submittedPhone !== '')
                    <p class="mt-2 text-sm text-stone-500">No WhatsApp: {{ $submittedPhone }}</p>
                @endif

                <div class="mt-6">
                    <a href="{{ route('public.booking.show', $token) }}" class="inline-flex px-5 py-2.5 rounded-xl bg-rose-500 text-white hover:bg-rose-600">
                        Kembali ke Form Booking
                    </a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
