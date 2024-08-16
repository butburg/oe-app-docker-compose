{{-- list comments --}}
@forelse ($post->comments->reverse() as $comment)
    <div class="text-comment-text rounded-lg bg-c-primary/40 p-3">
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
                        <x-text-button
                            class="rounded bg-c-secondary/50 p-2 text-c-text hover:bg-c-secondary/70 hover:underline"
                            :formId="'commentDelete_' . $comment->id">Delete</x-text-button>
                        <x-text-button
                            class="rounded bg-c-accent/50 p-2 text-c-text hover:bg-c-accent/70 hover:underline"
                            :formId="'commentUpdate_' . $comment->id">Update</x-text-button>
                    </div>
                </div>

            </div>
        @endif
    </div>
@empty
    <div class="text-comment-text rounded-lg bg-c-primary/40 p-3">
        @auth
            <p>Be the first one to comment.</p>
        @else
            <p>
                <a class="hover:text-nav-text text-primary rounded-md text-sm underline focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    href="{{ route('login') }}">
                    Login</a> and be the first one to comment.
            </p>
        @endauth
    </div>
@endforelse
