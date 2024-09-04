<a class="transform transition-transform hover:scale-105"
    href="{{ $comment->user ? route('profile.show', $comment->user) : '' }}">
    @include('components.image_or_placeholder', [
        'image' => $comment->user?->image,
        'size_type' => 'xs',
        'alt_title' => $comment->user?->name,
        'style' => 'h-10 w-10 rounded-full bg-gray-200 object-cover',
        'placeholder' => 'storage/files/images/starfish.svg',
    ])
</a>

<div class="text-title-text flex-grow text-sm font-semibold">
    @if ($comment->user)
        <a class="text-title-text flex-grow cursor-pointer text-sm font-semibold hover:underline"
            href="{{ route('profile.show', $comment->user) }}">
            {{ Auth::id() === $comment->user_id ? 'You' : Str::limit($comment->user?->name ?? 'Unknown', 30, '...') }}
        </a>
    @else
        <a class="text-title-text flex-grow cursor-pointer text-sm font-semibold text-gray-800 hover:underline"
            href="">
            Deleted User
        </a>
    @endif

    <p class="text-xs"
        title="Last update {{ $comment->updated_at->diffForHumans() }}">
        {{ $comment->created_at->diffInHours() < 24 ? $comment->created_at->diffForHumans() : $comment->created_at->format('M d, Y') }}
    </p>
</div>
