<img src="{{ $comment->user->profile_image ?? asset('storage/files/images/blue.jpg') }}" alt="Profile Image"
    class="w-8 h-8 rounded-full object-cover">
<div class="flex-grow text-sm font-semibold text-gray-900">
    {{ Auth::id() === $comment->user_id ? 'You' : $comment->user->name }}
    <span class="text-gray-500 text-xs ml-2">
        {{ $comment->created_at->diffInHours() < 24 ? $comment->created_at->diffForHumans() : $comment->created_at->format('M d, Y') }}
    </span>
</div>
