@php
    $profileImagePath = $comment->user->profile_image
        ? 'storage/' . $comment->user->profile_image
        : 'storage/files/images/blue.jpg';
@endphp

<img class="h-10 w-10 rounded-full object-cover" src="{{ asset($profileImagePath) }}" alt="{{ $comment->user->name }}">

<div class="flex-grow text-sm font-semibold text-title-text">
    {{ Auth::id() === $comment->user_id ? 'You' : $comment->user->name }}
    <p class="text-xs ">
        {{ $comment->created_at->diffInHours() < 24 ? $comment->created_at->diffForHumans() : $comment->created_at->format('M d, Y') }}
    </p>
</div>
