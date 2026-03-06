@props([
    'image',
    'size_type',
    'alt_title' => null,
    'style' => '',
    'placeholder' => 'storage/files/images/broken.png',
    'zoomable' => false,
    'full_size_type' => 'xl',
])

@php
    // Ensure $image is not null and retrieve the correct variant if $image exists
    $imagePath = $image && $image->variants ? $image->variants->firstWhere('size_type', $size_type)?->path : null;
    $zoomImagePath =
        $image && $image->variants
            ? $image->variants->firstWhere('size_type', $full_size_type)?->path ?? $imagePath
            : null;
    $hasImage = $imagePath && file_exists(public_path('storage/' . $imagePath));
    $resolvedZoomPath =
        $zoomImagePath && file_exists(public_path('storage/' . $zoomImagePath)) ? $zoomImagePath : $imagePath;
@endphp

@if ($hasImage)
    @if ($zoomable)
        <div x-data="{
            isOpen: false,
            scrollY: 0,
            open() {
                this.scrollY = window.scrollY || window.pageYOffset;
                document.body.style.overflow = 'hidden';
                this.isOpen = true;
            },
            close() {
                this.isOpen = false;
                document.body.style.overflow = '';
                window.scrollTo(0, this.scrollY);
            }
        }" class="w-full">
            <button type="button" @click="open()" class="block w-full" aria-label="Enlarge image">
                <img class="{{ $style }} cursor-zoom-in" src="{{ asset('storage/' . $imagePath) }}" title="{{ $alt_title }}"
                    alt="{{ $alt_title }}" loading="lazy">
            </button>

            <div x-cloak x-show="isOpen" x-transition.opacity.duration.150ms @click.self="close()"
                class="fixed inset-0 z-[70] bg-black/40 p-3 sm:p-6 flex items-center justify-center">
                <button type="button" @click="close()"
                    class="absolute right-3 top-3 rounded-md bg-black/60 px-3 py-1 text-sm text-white hover:bg-black/80"
                    aria-label="Close">Close</button>

                <img src="{{ asset('storage/' . $resolvedZoomPath) }}" alt="{{ $alt_title }}"
                    class="max-h-full max-w-full object-contain" @click.stop>
            </div>
        </div>
    @else
        <img class="{{ $style }}" src="{{ asset('storage/' . $imagePath) }}" title="{{ $alt_title }}"
            alt="{{ $alt_title }}" loading="lazy">
    @endif
@else
    <img class="{{ $style }}" src="{{ asset($placeholder) }}" title="{{ $alt_title }}"
        alt="Error. Image not found in storage/{{ $imagePath }}" loading="lazy">
@endif
