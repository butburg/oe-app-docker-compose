@props(['active', 'accent' => false])

{{-- accent: active bar in the accent color instead of primary --}}
@php
$classes = ($active ?? false)
    ? 'inline-flex items-center border-b-2 ' . ($accent ? 'border-c-accent' : 'border-c-primary') . ' px-1 pt-1 text-sm font-medium leading-5 text-c-text transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-c-primary'
    : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-c-muted transition-colors hover:border-c-line hover:text-c-text focus:outline-none focus-visible:ring-2 focus-visible:ring-c-primary';

@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
