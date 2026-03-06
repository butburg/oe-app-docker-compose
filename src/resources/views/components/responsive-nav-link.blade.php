@props(['active'])

@php
    $classes =
        $active ?? false
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-main-color text-start text-base font-semibold text-c-background bg-main-color-50 focus:outline-none focus:text-c-background focus:bg-main-color-100 focus:border-main-color-700 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-semibold text-c-background hover:text-c-background hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-c-background focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
