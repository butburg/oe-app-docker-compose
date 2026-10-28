<a class="transform transition-transform hover:scale-105"
    href="{{ $comment->user ? route('profile.show', $comment->user) : '' }}">
    @include('components.image_or_placeholder', [
        'image' => $comment->user?->image,
        'size_type' => 'xs',
        'alt_title' => $comment->user?->name,
        'style' => 'h-10 w-10 rounded-full bg-c-surface-raised object-cover',
        'placeholder' => 'images/starfish.svg',
    ])
</a>

<div class="text-title-text flex-grow text-sm font-semibold">
    @if ($comment->user)
        <a class="flex-grow cursor-pointer text-sm font-semibold text-c-text hover:underline"
            href="{{ route('profile.show', $comment->user) }}">
            {{ Auth::id() === $comment->user_id ? 'You' : Str::limit($comment->user?->name, config('app.truncate_name'), '...') }}
        </a>
    @else
        <a class="flex-grow cursor-pointer text-sm font-semibold text-c-muted hover:text-c-text hover:underline"
            href="">
            Deleted User
        </a>
    @endif

    <p class="text-xs text-c-muted"
        title="Last update {{ $comment->updated_at->diffForHumans() }}">
        {{ $comment->created_at->diffInHours() < 24 ? $comment->created_at->diffForHumans() : $comment->created_at->format('d.m.Y') }}
    </p>
</div>
