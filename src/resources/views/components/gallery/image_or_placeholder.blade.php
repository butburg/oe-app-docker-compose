@props(['image', 'style'])

@if (file_exists(public_path('storage/' . $image->image_file)))
    <img src="{{ asset('storage/' . $image->image_file) }}" alt="{{ $image->title }}" class="{{$style}}"
        @click="showComments = !showComments" loading="lazy">
@else
    <img src="{{ asset('storage/files/images/broken.png') }}" alt="Placeholder" class="{{$style}}"
        @click="showComments = !showComments" loading="lazy">
@endif
