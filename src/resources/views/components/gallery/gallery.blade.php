<div class="max-w-7xl mx-auto py-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach ($images as $image)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" x-data="{ showComments: false }">
                @if (@file_exists(public_path('storage/' . $image->image_file)))
                    <div class="relative mx-auto">
                        <img src="{{ asset('storage/' . $image->image_file) }}" alt="{{ $image->title }}"
                            class="w-full h-auto object-cover cursor-pointer" @click="showComments = !showComments">
                    </div>
                    <div class="p-2 text-right">
                        <span>
                            <h3 class="mt-2 font-medium leading-tight">{{ $image->title }}</h3>
                            <p class="text-sm">{{ $image->username }}</p>
                        </span>
                    </div>
                    {{-- Add Comment Form --}}
                    @if (Auth::check())
                        <form action="{{ route('comments.store') }}" method="POST" class="p-2 flex items-center mt-4">
                            @csrf
                            <input type="hidden" name="post_id" value="{{ $image->id }}">
                            <textarea class="flex-1 border rounded p-2 text-sm overflow-hidden resize-none" name="comment"
                                placeholder="Add comment..." required rows="1"
                                oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
                            <button class="p-2 ml-1 hover:bg-blue-100 rounded" type="submit">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M15.5 9.00001V15H8.5M8.5 15L9.5 14M8.5 15L9.5 16M13 5H17.5C18.0523 5 18.5 5.44772 18.5 6V18C18.5 18.5523 18.0523 19 17.5 19H6.5C5.94772 19 5.5 18.5523 5.5 18V12C5.5 11.4477 5.94772 11 6.5 11H12V6C12 5.44771 12.4477 5 13 5Z"
                                        stroke="#464455" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </form>
                    @endif
                    <div x-show="showComments" class="bg-gray-100 p-4 space-y-4">
                        @forelse ($image->comments->reverse() as $comment)
                            <div class=" bg-white p-3 rounded-lg">
                                @if (Auth::check() && Auth::id() === $comment->user_id)
                                    <div x-data="{ isEditing: false }">
                                        <div class="flex items-start justify-between space-x-3 mb-2">
                                            <img src="{{ $comment->user->profile_image ?? asset('storage/files/images/blue.jpg') }}"
                                                alt="Profile Image" class="w-8 h-8 rounded-full object-cover">
                                            <div class="flex-grow text-sm font-semibold text-gray-900">
                                                {{ Auth::id() === $comment->user_id ? 'You' : $comment->user->name }}
                                                <span class="text-gray-500 text-xs ml-2">
                                                    @if ($comment->created_at->diffInHours() < 24)
                                                        {{ $comment->created_at->diffForHumans() }}
                                                    @else
                                                        {{ $comment->created_at->format('M d, Y') }}
                                                    @endif
                                                </span>
                                            </div>
                                            <button @click="isEditing = !isEditing"
                                                class="px-3 py-1 text-blue-500 hover:bg-blue-100 rounded"
                                                type="button">
                                                <span x-show="!isEditing">Edit</span>
                                                <span x-show="isEditing">Cancel</span>
                                            </button>
                                        </div>
                                        <div x-show="!isEditing">
                                            <p class="text-gray-700">{{ $comment->comment }}</p>
                                        </div>
                                        <div x-show="isEditing">
                                            <form action="{{ route('comments.update', $comment->id) }}" method="POST"
                                                id="commentUpdate">
                                                @csrf
                                                @method('PUT')
                                                <textarea class="w-full border rounded p-2 text-sm overflow-hidden resize-none mr-2" name="comment"
                                                    oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'" placeholder="Update your comment...">{{ $comment->comment }}</textarea>
                                            </form>
                                            <form action="{{ route('comments.destroy', $comment->id) }}" method="POST"
                                                onsubmit="return confirm('Are you sure you want to delete this comment?');"
                                                id="commentDelete">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            <div class="flex items-center space-x-3">
                                                <button form="commentDelete"
                                                    class="px-3 py-1 text-red-500 hover:bg-red-100 rounded"
                                                    type="submit">Delete</button>
                                                <button form="commentUpdate"
                                                    class="px-3 py-1 ml-2 text-blue-500 hover:bg-blue-100 rounded"
                                                    type="submit">Update</button>

                                            </div>
                                        </div>
                                @else
                                <div class="flex items-start justify-between space-x-3 mb-2">
                                    <img src="{{ $comment->user->profile_image ?? asset('storage/files/images/blue.jpg') }}"
                                        alt="Profile Image" class="w-8 h-8 rounded-full object-cover">
                                    <div class="flex-grow text-sm font-semibold text-gray-900">
                                        {{ $comment->user->name }}
                                        <span class="text-gray-500 text-xs ml-2">
                                            @if ($comment->created_at->diffInHours() < 24)
                                                {{ $comment->created_at->diffForHumans() }}
                                            @else
                                                {{ $comment->created_at->format('M d, Y') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <x-truncated-comment>
                                    {{ $comment->comment }}
                                </x-truncated-comment>   
                                @endif
                            </div>
                    </div>
                @empty
                    <p class="text-gray-700">Be the first one to comment.</p>
                @endforelse
            </div>
        @endif
    </div>
    @endforeach
</div>
</div>