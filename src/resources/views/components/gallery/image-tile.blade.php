<div class="block y items-center w-full">
    <div class="flex items-center justify-center w-full bg-purple-300">
        <div class="flex items-center justify-center w-full">
        @if (file_exists(public_path('storage/' . $image->image_file)))
            <img src="{{ asset('storage/' . $image->image_file) }}" alt="{{ $image->title }}"
                 class="max-h-80 w-full" @click="showComments = !showComments">
        @else
            <img src="{{ asset('storage/files/images/broken.png') }}" alt="Placeholder"
                 class="max-h-80" @click="showComments = !showComments">
        @endif
        </div>
    </div>
    <div class="p-2 mt-4 md:mt-0 w-64 h-full ">
        <h5 class="mb-2 font-medium leading-tight">{{ $image->title }}</h5>
        <p class="mb-2 text-sm">{{ $image->username }}</p>
        <p class="text-neutral-300 text-sm">
            <small>Last updated {{ $image->updated_at->diffForHumans() }}</small>
        </p>
    </div>
</div>

<div class="block md:flex md:items-center md:justify-start md:space-x-1">
    <div class="flex items-center justify-center">
        @if (file_exists(public_path('storage/' . $image->image_file)))
            <img src="{{ asset('storage/' . $image->image_file) }}" alt="{{ $image->title }}"
                 class="max-h-80" @click="showComments = !showComments">
        @else
            <img src="{{ asset('storage/files/images/broken.png') }}" alt="Placeholder"
                 class="max-h-80" @click="showComments = !showComments">
        @endif
    </div>
    <div class="p-2 mt-4 md:mt-0">
        <h5 class="mb-2 font-medium leading-tight">{{ $image->title }}</h5>
        <p class="mb-2 text-sm">{{ $image->username }}</p>
        <p class="text-neutral-300 text-sm">
            <small>Last updated {{ $image->updated_at->diffForHumans() }}</small>
        </p>
    </div>
</div>

