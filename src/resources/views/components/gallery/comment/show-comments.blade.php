{{-- list comments --}}
@forelse ($image->comments->reverse() as $comment)
    <div class="rounded-lg bg-c-primary/40 text-comment-text p-3">
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
                    <x-text-button buttonType="editToggle"></x-text-button>
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
                        <x-text-button :formId="'commentDelete_' . $comment->id" class="rounded p-2 text-c-text hover:bg-c-secondary/70 hover:underline bg-c-secondary/50">Delete</x-text-button>
                        <x-text-button :formId="'commentUpdate_' . $comment->id" class="rounded p-2 text-c-text hover:bg-c-accent/70 hover:underline bg-c-accent/50">Update</x-text-button>
                    </div>
                </div>

            </div>
        @endif
    </div>
@empty
    <p>Be the first one to comment.</p>
@endforelse
