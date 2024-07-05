{{-- Add Comment Form --}}
@if (Auth::check())
<div class="flex items-center space-x-1" @click="showComments = true" >
    <x-gallery.form.form action="{{ route('comments.store') }}" method="POST"
        :formId="'commentAdd_' . $image->id">
        <input type="hidden" name="post_id" value="{{ $image->id }}">
        <x-gallery.form.textarea placeholder="Add comment..."
            name="comment"></x-gallery.form.textarea>
        @error('comment', 'commentAdd_' . $image->id)
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror
    </x-gallery.form.form>

    <x-text-button :formId="'commentAdd_' . $image->id">
        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path
                d="M 17.924 8.386 L 17.924 16.714 L 6.077 16.714 M 13.692 1 L 21.308 1 C 22.245 1 23 1.704 23 2.571 L 23 21.429 C 23 22.296 22.245 23 21.308 23 L 2.692 23 C 1.757 23 1 22.296 1 21.429 L 1 12 C 1 11.133 1.757 10.429 2.692 10.429 L 12 10.429 L 12 2.571 C 12 1.704 12.757 1 13.692 1 Z"
                style="stroke-linecap: round; stroke-linejoin: round; paint-order: fill; stroke: rgb(0, 0, 0); fill: rgba(255, 255, 255, 0); fill-rule: nonzero;"
                transform="matrix(1, 0, 0, 1, 0, 8.881784197001252e-16)" />
            <polyline
                style="fill: rgba(255, 255, 255, 0); stroke: rgb(0, 0, 0); stroke-linecap: round; stroke-linejoin: round; paint-order: fill;"
                points="9 19.9 6 16.700000762939453 8.972000122070312 13.538000106811523"
                transform="matrix(1, 0, 0, 1, 0, 8.881784197001252e-16)" />
        </svg>
    </x-text-button>
</div>
@endif