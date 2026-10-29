@props([
    'image',
    'size_type',
    'alt_title' => null,
    'style' => '',
    'placeholder' => 'images/broken.png',
    'zoomable' => false,
    'full_size_type' => 'xl',
])

@php
    // Links go through /img/{variant}/{token} and expire (FEAT-011), the DB row tells if a variant exists
    $imageUrl = $image?->url($size_type);
    $zoomUrl = $image?->url($full_size_type) ?? $imageUrl;
    $hasImage = $imageUrl !== null;
@endphp

@if ($hasImage)
    @if ($zoomable)
        <div x-data="{
            isOpen: false,
            scrollY: 0,
            toggle() {
                if (this.isOpen) {
                    this.isOpen = false;
                    document.body.style.overflow = '';
                    window.scrollTo(0, this.scrollY);
                } else {
                    this.scrollY = window.scrollY || window.pageYOffset;
                    document.body.style.overflow = 'hidden';
                    this.isOpen = true;
                }
            }
        }" class="contents">
            <img @click="toggle()" @keydown.enter.prevent="toggle()" @keydown.space.prevent="toggle()"
                class="{{ $style }} cursor-zoom-in" src="{{ $imageUrl }}"
                title="{{ $alt_title }}" alt="{{ $alt_title }}" loading="lazy" role="button" tabindex="0"
                aria-label="Enlarge image" data-protected>

            <div x-cloak x-show="isOpen" x-transition.opacity.duration.150ms @click="toggle()"
                class="fixed inset-0 z-[70] bg-black/40 p-3 sm:p-6 flex items-center justify-center">
                <img @click.stop="toggle()" src="{{ $zoomUrl }}" alt="{{ $alt_title }}"
                    class="max-h-full max-w-full object-contain cursor-zoom-out" data-protected>
            </div>
        </div>
    @else
        <img class="{{ $style }}" src="{{ $imageUrl }}" title="{{ $alt_title }}"
            alt="{{ $alt_title }}" loading="lazy" data-protected>
    @endif
@else
    <img class="{{ $style }}" src="{{ asset($placeholder) }}" title="{{ $alt_title }}"
        alt="Image not found" loading="lazy">
@endif
