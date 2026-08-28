@php
    $hotFilePath = public_path('hot');
    $manifestPath = public_path('build/manifest.json');
    $host = request()->getHost();
    $isLoopbackHost = in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    $shouldUseDevServer = file_exists($hotFilePath) && $isLoopbackHost;
@endphp

@if ($shouldUseDevServer)
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@elseif (file_exists($manifestPath))
    @php
        $manifest = json_decode(file_get_contents($manifestPath), true) ?? [];
        $cssAsset = $manifest['resources/css/app.css']['file'] ?? null;
        $jsAsset = $manifest['resources/js/app.js']['file'] ?? null;
    @endphp
    @if ($cssAsset)
        <link rel="stylesheet" href="{{ asset('build/' . $cssAsset) }}">
    @endif
    @if ($jsAsset)
        <script type="module" src="{{ asset('build/' . $jsAsset) }}"></script>
    @endif
@else
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endif
