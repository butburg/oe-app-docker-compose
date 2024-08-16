@props(['image', 'style', 'size_type'])

@php
    //Ensure $image exists and retrieve the correct variant
    $variant = $image ? $image->variants->firstWhere('size_type', $size_type) : null;
@endphp

@if ($variant && file_exists(public_path('storage/' . $variant->path)))
    <img class="{{ $style }}" src="{{ asset('storage/' . $variant->path) }}" alt="{{ $image->title }}"
        loading="lazy">
@else
    <img class="{{ $style }}" src="{{ asset('storage/files/images/broken.png') }}" alt="Placeholder" loading="lazy">
@endif
