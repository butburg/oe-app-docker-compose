@php
    $page = app('App\Http\Controllers\PostController')->getPageForPost($post, config('app.posts_per_page'));
    $shareUrl = route('dashboard') . "?page={$page}#post-{$post->id}";
@endphp

<button type="button" class="share-button inline-flex h-8 w-8 items-center justify-center rounded-md text-c-muted transition-colors hover:bg-c-surface-raised hover:text-c-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-c-primary" data-share-url="{{ $shareUrl }}" data-share-title="{{ $post->title }}"
    data-share-app-name="{{ config('app.name') }}" title="Share Link" aria-label="Share Post">
    <x-icons.share class="mx-1 -mb-0.5" aria-hidden="true" />
</button>
