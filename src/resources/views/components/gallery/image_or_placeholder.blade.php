@props(['image', 'style'])

@if (file_exists(public_path('storage/' . $image->image_file)))
    <img class="{{ $style }}" src="{{ asset('storage/' . $image->image_file) }}" alt="{{ $image->title }}"
        @click="showComments = !showComments" loading="lazy">
@else
    <img class="{{ $style }}" src="{{ asset('storage/files/images/broken.png') }}" alt="Placeholder"
        @click="showComments = !showComments" loading="lazy">
@endif
