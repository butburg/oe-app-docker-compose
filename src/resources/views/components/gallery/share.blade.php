@php
    $page = app('App\Http\Controllers\PostController')->getPageForPost(
        $post,
        config('app.posts_per_page'),
    );
    $shareUrl = route('dashboard') . "?page={$page}#post-{$post->id}";
@endphp

<!-- SVG Button for Sharing -->
<button class="share-button" data-share-url="{{ $shareUrl }}"
    title="Share Link" aria-label="Share Post">
    <!-- SVG Content Here -->
    <svg class="mx-1 -mb-0.5" xmlns="http://www.w3.org/2000/svg" width="12"
        height="12" fill="currentColor" viewBox="0 0 16 16">
        <path
            d="M11 2.5a2.5 2.5 0 1 1 .603 1.628l-6.718 3.12a2.5 2.5 0 0 1 0 1.504l6.718 3.12a2.5 2.5 0 1 1-.488.876l-6.718-3.12a2.5 2.5 0 1 1 0-3.256l6.718-3.12A2.5 2.5 0 0 1 11 2.5" />
    </svg>
</button>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.share-button').forEach(function(
            button) {
            button.addEventListener('click', function() {
                const shareUrl = this.getAttribute(
                    'data-share-url');

                // Check if the browser supports the Share API
                if (navigator.share) {
                    navigator.share({
                        title: 'Check out this post from {{ config('app.name') }}',
                        url: shareUrl,
                    }).then(() => {
                        console.log(
                            'Thanks for sharing!'
                        );
                    }).catch(console.error);
                } else {
                    // Fallback to copying the link to the clipboard
                    navigator.clipboard.writeText(
                        shareUrl).then(() => {
                        alert(
                            'Link copied to clipboard!'
                        );
                    }).catch(console.error);
                }
            });
        });
    });
</script>
