@php
    $page = app('App\Http\Controllers\PostController')->getPageForPost($post, config('app.posts_per_page'));
    $shareUrl = route('dashboard') . "?page={$page}#post-{$post->id}";
@endphp

<button type="button" class="share-button" data-share-url="{{ $shareUrl }}" data-share-title="{{ $post->title }}"
    data-share-app-name="{{ config('app.name') }}" title="Share Link" aria-label="Share Post">
    <x-icons.share class="mx-1 -mb-0.5" aria-hidden="true" />
</button>
