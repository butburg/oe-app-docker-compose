@php
    $profileImagePath = $comment->user->profile_image
        ? 'storage/' . $comment->user->profile_image
        : 'storage/files/images/blue.jpg';
@endphp

<img class="h-10 w-10 rounded-full object-cover" src="{{ asset($profileImagePath) }}" alt="{{ $comment->user->name }}">

<div class="flex-grow text-sm font-semibold text-gray-900">
    {{ Auth::id() === $comment->user_id ? 'You' : $comment->user->name }}
    <p class="text-xs text-gray-500">
        {{ $comment->created_at->diffInHours() < 24 ? $comment->created_at->diffForHumans() : $comment->created_at->format('M d, Y') }}
    </p>
</div>
