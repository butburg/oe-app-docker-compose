@props(['active', 'accent' => false])

@php
    $classes =
        $active ?? false
            ? 'block w-full border-l-2 ' . ($accent ? 'border-c-accent' : 'border-c-primary') . ' bg-c-surface-raised px-3 py-2 text-start text-sm font-semibold text-c-text transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-c-primary'
            : 'block w-full border-l-2 border-transparent px-3 py-2 text-start text-sm font-medium text-c-muted transition-colors hover:border-c-line hover:bg-c-surface-raised hover:text-c-text focus:outline-none focus-visible:ring-2 focus-visible:ring-c-primary';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
