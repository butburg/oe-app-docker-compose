@props(['image', 'style', 'size_type', 'alt_title' => null])

@php
    // Ensure $image is not null and retrieve the correct variant if $image exists
    $imagePath = $image && $image->variants ? $image->variants->firstWhere('size_type', $size_type)?->path : null;
@endphp

@if ($imagePath && file_exists(public_path('storage/' . $imagePath)))
    <img class="{{ $style }}" src="{{ asset('storage/' . $imagePath) }}" title="{{ $alt_title }}"
        alt="{{ $alt_title }}" loading="lazy">
@else
    <img class="{{ $style }} rounded-none object-scale-down" src="{{ asset('storage/files/images/broken.png') }}"
        title="{{ $alt_title }} - Not Found Error" alt="Image not found." loading="lazy">
@endif
