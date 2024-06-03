<div class="max-w-7xl mx-auto py-6">
    <h3 class="text-2xl px-4 sm:px-0 font-semibold mb-4">Image Gallery</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach ($images as $image)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                {{-- Check if the image URL is valid --}}
                @if (@file_exists(public_path('storage/' . $image->image_file)))
                    {{-- Display the image if the URL is valid --}}
                    <div class="relative  mx-auto">
                        <img src="{{ asset('storage/' . $image->image_file) }}" alt="{{ $image->title }}"
                            class="w-full h-auto object-cover">
                        <div class="absolute inset-0 bg-gray-700 opacity-60"></div>
                        <div class="absolute inset-0 p-4 flex flex-col justify-end">
                            <h4 class="text-white font-semibold mb-2">Comments</h4>
                            <div class="space-y-4">
                                @forelse ($image->comments as $comment)
                                    <div class="flex items-start space-x-3 bg-white bg-opacity-75 p-3 rounded-lg">
                                        <img src="{{ $comment->user->profile_image ?? asset('storage/files/images/blue.jpg') }}" alt="Profile Image" class="w-8 h-8 rounded-full object-cover">
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">
                                                {{ Auth::id() === $comment->user_id ? 'You' : $comment->user->name }}
                                                <span class="text-gray-500 text-xs ml-2">{{ $comment->created_at->format('M d, Y') }}</span>
                                            </div>
                                            <p class="text-gray-700">{{ $comment->comment }}</p>
                                            @if (Auth::check() && Auth::id() === $comment->user_id)
                                                <div x-data="{ isEditing: false }" class="mt-2">
                                                    <button @click="isEditing = !isEditing" class="text-blue-500 hover:underline text-xs">
                                                        <span x-show="!isEditing">Edit</span>
                                                        <span x-show="isEditing">Cancel</span>
                                                    </button>
                                                    <div x-show="isEditing" class="mt-2">
                                                        <form action="{{ route('comments.update', $comment->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <textarea class="w-full border rounded p-2 text-sm" name="comment" required>{{ $comment->comment }}</textarea>
                                                            @error('comment')
                                                                <div class="text-red-600 text-sm">{{ $message }}</div>
                                                            @enderror
                                                            <button class="text-blue-500 hover:underline text-xs mt-1" type="submit">Update</button>
                                                        </form>
                                                        <form action="{{ route('comments.destroy', $comment->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this comment?');" class="mt-1">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="text-red-500 hover:underline text-xs" type="submit">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-white">Be the first one to comment.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Display a placeholder image if the URL is not valid --}}
                    <img src="{{ asset('storage/files/images/broken.png') }}" alt="Placeholder" class="h-10">
                @endif
                <div class="p-2">
                    <span>
                        <h3 class="mt-2 font-medium leading-tight">{{ $image->title }}</h3>
                        <p class="text-sm">{{ $image->username }}</p>
                    </span>

                    {{-- Add Comment Form --}}
                    @if (Auth::check())
                        <form action="{{ route('comments.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="post_id" value="{{ $image->id }}">
                            <textarea class="w-full" name="comment" required></textarea>
                            <button class="text-blue-500 hover:underline" type="submit">Add Comment</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
