{{-- list comments --}}
@forelse ($post->comments->reverse() as $comment)
    {{-- ->reverse() --}}
    <div class="rounded-md border border-c-line bg-c-surface-raised p-3 text-c-text">
        @if (auth()->check() && auth()->user()->id === $comment->user_id)
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
                            <div class="alert alert-danger" data-form-error role="alert">{{ $message }}
                            </div>
                        @enderror
                    </x-gallery.form.form>
                    <x-gallery.form.form action="{{ route('comments.destroy', $comment->id) }}" method="DELETE"
                        :formId="'commentDelete_' . $comment->id">
                    </x-gallery.form.form>
                    <div class="mt-1 flex items-center space-x-3">
                        <x-text-button
                            class="app-button-danger h-9 min-h-9 px-3"
                            :formId="'commentDelete_' . $comment->id">Delete</x-text-button>
                        <x-text-button
                            class="app-button-primary h-9 min-h-9 px-3"
                            :formId="'commentUpdate_' . $comment->id">Update</x-text-button>
                    </div>
                </div>
            </div>
        @else
            <div class="mb-2 flex items-start justify-between space-x-3">
                <x-gallery.comment.comment-creator :comment="$comment" />
            </div>
            <x-truncated-comment>
                {{ $comment->comment }}
            </x-truncated-comment>
        @endif
    </div>
@empty
    <div class="rounded-md border border-c-line bg-c-surface-raised p-3 text-c-muted">
        <p>Be the first one to comment.</p>
    </div>
@endforelse
