<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tautan Booking Kedaluwarsa</title>
    @include('partials.vite-assets')
</head>
<body class="font-sans bg-gradient-to-b from-rose-50 via-amber-50 to-white min-h-screen">
    <main class="py-16 px-[18px] sm:px-6">
        <div class="max-w-lg mx-auto bg-white rounded-2xl border border-rose-100 shadow-xl p-8 text-center">
            <h1 class="text-2xl font-semibold text-stone-900">Tautan Booking Kedaluwarsa</h1>
            <p class="mt-3 text-stone-600">
                {{ $message ?? 'Form booking ini sudah tidak aktif. Silakan minta MUA mengirim tautan baru.' }}
            </p>
        </div>
    </main>
</body>
</html>


