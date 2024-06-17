<div class="max-w-7xl mx-auto py-6">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @foreach ($images->reverse() as $image)
            <div class="bg-gray-600 overflow-hidden shadow-sm sm:rounded-lg" x-data="{ showComments: false }">
                <x-gallery.image-tile :image="$image" />
                

                {{-- show Comments pop out --}}
                <div x-show="showComments"
                    class="absolute left-0 right-0 bg-gray-100 p-4 space-y-4 z-10 overflow-auto max-h-80"
                    @click.away="showComments = false">

                    {{-- Add Comment Form --}}
                    @if (Auth::check())
                        <div class="flex items-center space-x-1 p-2">
                            <x-gallery.form.form action="{{ route('comments.store') }}" method="POST" :formId="'commentAdd_' . $image->id">
                                <input type="hidden" name="post_id" value="{{ $image->id }}">
                                <x-gallery.form.textarea placeholder="Add comment..."
                                    name="comment"></x-gallery.form.textarea>
                                @error('comment', 'commentAdd_' . $image->id)
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror

                            </x-gallery.form.form>

                            <x-text-button color="fuchsia" :formId="'commentAdd_' . $image->id">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M 17.924 8.386 L 17.924 16.714 L 6.077 16.714 M 13.692 1 L 21.308 1 C 22.245 1 23 1.704 23 2.571 L 23 21.429 C 23 22.296 22.245 23 21.308 23 L 2.692 23 C 1.757 23 1 22.296 1 21.429 L 1 12 C 1 11.133 1.757 10.429 2.692 10.429 L 12 10.429 L 12 2.571 C 12 1.704 12.757 1 13.692 1 Z"
                                        style="stroke-linecap: round; stroke-linejoin: round; paint-order: fill; stroke: rgb(0, 0, 0); fill: rgba(255, 255, 255, 0); fill-rule: nonzero;"
                                        transform="matrix(1, 0, 0, 1, 0, 8.881784197001252e-16)" />
                                    <polyline
                                        style="fill: rgba(255, 255, 255, 0); stroke: rgb(0, 0, 0); stroke-linecap: round; stroke-linejoin: round; paint-order: fill;"
                                        points="9 19.9 6 16.700000762939453 8.972000122070312 13.538000106811523"
                                        transform="matrix(1, 0, 0, 1, 0, 8.881784197001252e-16)" />
                                </svg>
                            </x-text-button>
                        </div>
                    @endif

                    {{-- list comments --}}
                    @forelse ($image->comments->reverse() as $comment)
                        <div class="bg-white p-3 rounded-lg">
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
                                        <x-text-button color="blue" buttonType="editToggle"></x-text-button>
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
                                        <div class="mt-1 flex items-center space-x-3">
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
                </div> {{-- show comments pop out --}}

            </div>
        @endforeach {{-- images as image --}}
    </div>
</div>
