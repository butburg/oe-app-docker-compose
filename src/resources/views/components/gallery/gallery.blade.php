<div class="max-w-7xl mx-auto py-6">
    <h3 class="text-2xl px-4 sm:px-0 font-semibold mb-4">Image Gallery</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach ($images as $image)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-2">

                {{-- Check if the image URL is valid --}}
                @if (@file_exists(public_path('storage/' . $image->info_file)))
                    {{-- Display the image if the URL is valid --}}
                    <img src="{{ asset('storage/' . $image->info_file) }}" alt="{{ $image->title }}" class="w-full h-auto">
                @else
                    {{-- Display a placeholder image if the URL is not valid --}}
                    <img src="{{ asset('storage/files/images/broken.png') }}" alt="Placeholder" class="h-10">
                @endif
                <span>
                    <h3 class="mt-2 font-medium leading-tight">{{ $image->title }}</h3>
                    <p class="text-sm">{{ $image->username }}</p>
                </span>
                <h3 class="mt-2">Comments:</h3>
                {{-- Display Comments --}}
                @forelse ($image->comments as $comment)
                <div>
                    <p>{{ $comment->comment }}</p>
                    <small>By {{ $comment->user->name }}</small>
                </div>
                @empty
                No published posts found.
                @endforelse
                
                {{-- Add Comment Form --}}
                @if (Auth::check())
                <form action="{{ route('comments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="post_id" value="{{ $image->id }}">
                    <textarea name="comment" required></textarea>
                    <button type="submit">Add Comment</button>
                </form>
                @endif
                
            </div>
                @endforeach
    </div>
</div>
