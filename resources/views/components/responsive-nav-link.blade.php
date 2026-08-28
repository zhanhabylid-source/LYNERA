@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-start text-sm font-semibold text-rose-700 focus:outline-none'
            : 'block w-full rounded-xl border border-transparent px-4 py-3 text-start text-sm font-medium text-stone-700 hover:border-rose-100 hover:bg-rose-50 focus:outline-none transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
