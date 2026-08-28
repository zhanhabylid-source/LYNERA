@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center border-b-2 border-rose-500 px-1 py-5 text-sm font-semibold leading-5 text-rose-700 focus:outline-none focus:border-rose-600 transition'
            : 'inline-flex items-center border-b-2 border-transparent px-1 py-5 text-sm font-medium leading-5 text-stone-600 hover:border-rose-200 hover:text-rose-600 focus:outline-none focus:border-rose-300 focus:text-rose-600 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
