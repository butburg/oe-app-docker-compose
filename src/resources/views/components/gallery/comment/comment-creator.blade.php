@php
    $imagePath = $comment->user?->image?->variants->firstWhere('size_type', 'xs')->path;
@endphp

@if ($imagePath)
    <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . $imagePath) }}" alt="Profile Image"
        loading="lazy">
@else
    <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/files/images/blue.jpg') }}" alt="Profile Image"
        loading="lazy">
@endif

<div class="text-title-text flex-grow text-sm font-semibold">
    {{ Auth::id() === $comment->user_id ? 'You' : Str::limit($comment->user?->name ?? 'Unknown', 30, '...') }}
    <p class="text-xs" title="Last update {{ $comment->updated_at->diffForHumans() }}">
        {{ $comment->created_at->diffInHours() < 24 ? $comment->created_at->diffForHumans() : $comment->created_at->format('M d, Y') }}
    </p>
</div>
