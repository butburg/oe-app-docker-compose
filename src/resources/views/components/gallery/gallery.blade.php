<div class="max-w-7xl mx-auto py-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach ($images as $image)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" x-data="{ showComments: false }">
                <x-gallery.image-tile :image="$image" />

                {{-- Add Comment Form --}}
                @if (Auth::check())
                    <div class="flex items-center space-x-1">
                        <x-gallery.form.form action="{{ route('comments.store') }}" method="POST" :formId="'commentAdd_' . $image->id">
                            <input type="hidden" name="post_id" value="{{ $image->id }}">
                            <x-gallery.form.textarea placeholder="Add comment..."
                                name="comment"></x-gallery.form.textarea>
                            @error('comment', 'commentAdd_' . $image->id)
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </x-gallery.form.form>

                        <x-text-button color="red" :formId="'commentAdd_' . $image->id">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path stroke="#464455" stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.5 9.00001V15H8.5M8.5 15L9.5 14M8.5 15L9.5 16M13 5H17.5C18.0523 5 18.5 5.44772 18.5 6V18C18.5 18.5523 18.0523 19 17.5 19H6.5C5.94772 19 5.5 18.5523 5.5 18V12C5.5 11.4477 5.94772 11 6.5 11H12V6C12 5.44771 12.4477 5 13 5Z" />
                            </svg>
                        </x-text-button>
                    </div>
                @endif

                {{-- show Comments --}}
                <div x-show="showComments" class="bg-gray-100 p-4 space-y-4">
                    @forelse ($image->comments->reverse() as $comment)
                        <div class=" bg-white p-3 rounded-lg">
                            @if (!(Auth::id() === $comment->user_id))
                                <div class="flex items-start justify-between space-x-3 mb-2">
                                    <x-gallery.comment-creator :comment="$comment" />
                                </div>
                                <x-truncated-comment>
                                    {{ $comment->comment }}
                                </x-truncated-comment>
                            @else
                                {{-- edit users own Comments --}}
                                <div x-data="{ isEditing: false }">
                                    <div class="flex items-start justify-between space-x-3 mb-2">
                                        <x-gallery.comment-creator :comment="$comment" />
                                        <button @click="isEditing = !isEditing"
                                            class="px-3 py-1 text-blue-500 hover:bg-blue-100 rounded" type="button">
                                            <span x-show="!isEditing">Edit</span>
                                            <span x-show="isEditing">Cancel</span>
                                        </button>
                                    </div>

                                    <div x-show="!isEditing">
                                        <x-truncated-comment>
                                            {{ $comment->comment }}
                                        </x-truncated-comment>
                                    </div>

                                    <div x-show="isEditing">
                                        <x-gallery.form.form action="{{ route('comments.update', $comment->id) }}"
                                            method="PUT" :formId="'commentUpdate_' . $comment->id">
                                            <x-gallery.form.textarea placeholder="Edit comment..."
                                                name="comment">{{ $comment->comment }}</x-gallery.form.textarea>
                                            {{-- error msg here with named error bags --}}
                                            @error('comment', 'commentUpdate_' . $comment->id)
                                                <div class="alert alert-danger">{{ $message }}</div>
                                            @enderror
                                        </x-gallery.form.form>
                                        <x-gallery.form.form action="{{ route('comments.destroy', $comment->id) }}"
                                            method="DELETE" :formId="'commentDelete_' . $comment->id">
                                        </x-gallery.form.form>
                                        <div class="flex items-center space-x-3">
                                            <x-text-button color="red" :formId="'commentDelete_' . $comment->id">Delete</x-text-button>
                                            <x-text-button color="blue" :formId="'commentUpdate_' . $comment->id">Update</x-text-button>
                                        </div>
                                    </div>

                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-700">Be the first one to comment.</p>
                    @endforelse
                </div> {{-- show comments --}}

            </div>
        @endforeach {{-- images as image --}}
    </div>
</div>
