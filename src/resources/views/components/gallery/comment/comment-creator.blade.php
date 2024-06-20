<img class="h-8 w-8 rounded-full object-cover"
    src="{{ $comment->user->profile_image ?? asset('storage/files/images/blue.jpg') }}" alt="Profile Image">
<div class="flex-grow text-sm font-semibold text-gray-900">
    {{ Auth::id() === $comment->user_id ? 'You' : $comment->user->name }}
    <span class="ml-2 text-xs text-gray-500">
        {{ $comment->created_at->diffInHours() < 24 ? $comment->created_at->diffForHumans() : $comment->created_at->format('M d, Y') }}
    </span>
</div>
