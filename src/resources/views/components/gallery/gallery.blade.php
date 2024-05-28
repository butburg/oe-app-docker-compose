<div class="max-w-7xl mx-auto py-6">
    <h3 class="text-2xl px-4 sm:px-0 font-semibold mb-4">Image Gallery</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach ($images as $image)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-2">

                {{-- Check if the image URL is valid --}}
                @if (@file_exists(public_path('storage/' . $image->image_file)))
                    {{-- Display the image if the URL is valid --}}
                    <img src="{{ asset('storage/' . $image->image_file) }}" alt="{{ $image->title }}" class="w-full h-auto">
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
                    <div x-data="{ isEditing: false }">
                        <div x-show="!isEditing">
                            <p>{{ $comment->comment }}</p>
                            <small>By {{ (Auth::id() === $comment->user_id) ? 'You' : $comment->user->name }}</small>
                        </div>
                        @if (Auth::check() && Auth::id() === $comment->user_id)
                            {{-- Edit Comment  --}}
                            
                            <div x-show="isEditing">
                                {{-- Update Comment Form --}}
                                <form action="{{ route('comments.update', $comment->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <textarea class="w-full" name="comment" required>{{ $comment->comment }}</textarea>
                                    @error('comment')
                                        <div class="mb-2 text-red-600">{{ $message }}</div>
                                    @enderror
                                    <button class="text-blue-500 hover:underline" type="submit">Update</button>
                                </form>

                                {{-- Delete Comment Form --}}
                                <form action="{{ route('comments.destroy', $comment->id) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this comment?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-blue-500 hover:underline" type="submit">Delete</button>
                                </form>
                            </div>
                                <button @click="isEditing = !isEditing" class="text-blue-500 hover:underline">
                                    <span x-show="!isEditing">Edit</span>
                                    <span x-show="isEditing">Abort</span>
                                </button>
                        @endif

                    </div>
                @empty
                    <p>Be the first one to comment.</p>
                @endforelse

                {{-- Add Comment Form --}}
                @if (Auth::check())
                    <form action="{{ route('comments.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="post_id" value="{{ $image->id }}">
                        <textarea  class="w-full" name="comment" required></textarea>
                        <button class="text-blue-500 hover:underline" type="submit">Add Comment</button>
                    </form>
                @endif

            </div>
        @endforeach
    </div>
</div>
