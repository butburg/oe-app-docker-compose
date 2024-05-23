<div class="max-w-7xl mx-auto py-6">
    <h3 class="text-2xl px-4 sm:px-0 font-semibold mb-4">Image Gallery</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($images as $image)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-2">
                <img src="{{ asset('storage/' . $image->info_file) }}" alt="{{ $image->title }}" class="w-full h-auto">
                <span>
                    <h3 class="mt-2 font-medium leading-tight">{{ $image->title }}</h3>
                    <p class="text-sm">{{ $image->username }}</p>
                </span>
            </div>
        @endforeach
    </div>
</div>