{{-- list comments --}}
@forelse ($image->comments->reverse() as $comment)
    <div class="rounded-lg bg-comment-bg p-3">
        @if (!(Auth::id() === $comment->user_id))
            <div class="mb-2 flex items-start justify-between space-x-3">
                <x-gallery.comment.comment-creator :comment="$comment" />
            </div>
            <x-truncated-comment>
                {{ $comment->comment }}
            </x-truncated-comment>
        @else
            {{-- edit users own Comments --}}
            <div x-data="{ isEditing: false }">
                <div class="mb-2 flex items-start justify-between space-x-3">
                    <x-gallery.comment.comment-creator :comment="$comment" />
                    <x-text-button color="blue" buttonType="editToggle"></x-text-button>
                </div>

                <div x-show="!isEditing">
                    <x-truncated-comment>
                        {{ $comment->comment }}
                    </x-truncated-comment>
                </div>

                <div x-show="isEditing">
                    <x-gallery.form.form action="{{ route('comments.update', $comment->id) }}" method="PUT"
                        :formId="'commentUpdate_' . $comment->id">
                        <x-gallery.form.textarea name="comment"
                            placeholder="Edit comment...">{{ $comment->comment }}</x-gallery.form.textarea>
                        {{-- error msg here with named error bags --}}
                        @error('comment', 'commentUpdate_' . $comment->id)
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </x-gallery.form.form>
                    <x-gallery.form.form action="{{ route('comments.destroy', $comment->id) }}" method="DELETE"
                        :formId="'commentDelete_' . $comment->id">
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
    <p>Be the first one to comment.</p>
@endforelse
