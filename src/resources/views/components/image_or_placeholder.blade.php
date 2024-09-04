@props([
    'image',
    'size_type',
    'alt_title' => null,
    'style' => '',
    'placeholder' => 'storage/files/images/broken.png',
])

@php
    // Ensure $image is not null and retrieve the correct variant if $image exists
    $imagePath =
        $image && $image->variants
            ? $image->variants->firstWhere('size_type', $size_type)?->path
            : null;
@endphp

@if ($imagePath && file_exists(public_path('storage/' . $imagePath)))
    <img class="{{ $style }}" src="{{ asset('storage/' . $imagePath) }}"
        title="{{ $alt_title }}" alt="{{ $alt_title }}" loading="lazy">
@else
    <img class="{{ $style }}" src="{{ asset($placeholder) }}"
        title="{{ $alt_title }}" alt="Error. Image not found." loading="lazy">
@endif
