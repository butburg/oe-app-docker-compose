@props(['user', 'height' => '24', 'width' => '24', 'size' => 'l'])

{{-- Used for the public profile user image --}}

@php
 $isOnline = $user->session ? 'border-4 border-green-500' : '';
@endphp

<a class="block" href="{{ route('profile.show', $user->id) }}">
    <div class="relative">
        @include('components.image_or_placeholder', [
            'image' => $user->image,
            'size_type' => $size, // Ensure this matches the `size_type` in image_or_placeholder
            'alt_title' => $user->name,
            'style' => "h-{$height} w-{$width} {$isOnline} rounded-full bg-gray-200 object-cover",
            'placeholder' => 'storage/files/images/starfish.svg',
        ])
    </div>
</a>
