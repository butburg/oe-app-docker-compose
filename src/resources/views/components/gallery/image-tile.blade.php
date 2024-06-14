<div class="relative mx-auto">
    @if (file_exists(public_path('storage/' . $image->image_file)))
        <img src="{{ asset('storage/' . $image->image_file) }}" alt="{{ $image->title }}"
             class="w-full h-auto object-cover cursor-pointer" @click="showComments = !showComments">
    @else
        <img src="{{ asset('storage/files/images/broken.png') }}" alt="Placeholder"
             class="w-full h-auto object-cover cursor-pointer" @click="showComments = !showComments">
    @endif
</div>
<div class="p-2 text-right">
    <span>
        <h3 class="mt-2 font-medium leading-tight">{{ $image->title }}</h3>
        <p class="text-sm">{{ $image->username }}</p>
    </span>
</div>