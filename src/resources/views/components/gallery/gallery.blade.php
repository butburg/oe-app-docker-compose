<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('comments', {
            visibility: {}, // Store visibility states by post ID
            init(postId) {
                // Initialize the visibility state for each post
                if (!this.visibility[postId]) {
                    this.visibility[postId] = window.innerWidth >= 768;
                }
            },
            toggle(postId) {
                // toggle the visibility state for a specific post, but only in mobile
                this.visibility[postId] = !this.visibility[postId] || window.innerWidth >= 768;
            },
            updateOnResize(postId) {
                // Update visibility based on screen size
                this.visibility[postId] = window.innerWidth >= 768;
            }
        });
    });
</script>

<div class="mx-auto py-4 sm:py-5">
    <div class="{{-- grid grid-cols-1 lg:grid-cols-2 gap-4 --}}">
        @foreach ($posts as $post)
            <div id="{{ $post->anchor() }}" x-data x-init="$store.comments.init({{ $post->id }});
            window.addEventListener('resize', () => $store.comments.updateOnResize({{ $post->id }}))">

                <main class="app-surface mb-4 rounded-none border-x-0 border-c-line/20 px-2 py-2 sm:mx-1 sm:mb-6 sm:rounded-lg sm:border-x sm:px-6 sm:py-3">
                    <div class="mx-auto grid grid-cols-1 md:grid-cols-3 md:gap-x-10">
                        <!-- Image -->
                        <div class="col-span-3 flex items-center justify-center md:col-span-2 relative">
                            @include('components.image_or_placeholder', [
                                'image' => $post->image,
                                'size_type' => 'l', // Beispiel für einen Bildtyp
                                'alt_title' => $post->title,
                                'style' => 'max-h-[66vh]',
                                'zoomable' => true,
                            ])
                        </div>

                        <!-- Text and Comments -->
                        <div class="relative col-span-3 flex flex-col py-3 md:col-span-1">
                            {{-- thin line in the middle of the gap, fades out at the ends --}}
                            <span class="absolute -left-5 inset-y-4 hidden w-px bg-gradient-to-b from-transparent via-c-quiet/60 to-transparent md:block"
                                aria-hidden="true"></span>
                            <!-- Title and Author -->
                            <div class="flex flex-col rounded-lg bg-none">
                                <h1 class="break-words text-lg font-semibold leading-snug text-c-text md:text-xl">
                                    {{ $post->title }}
                                </h1>

                                @if ($post->user)
                                    <p class="mt-1 text-sm font-medium text-c-muted" title="{{ $post->user->name }}">
                                        <a class="cursor-pointer hover:underline"
                                            href="{{ route('profile.show', $post->user) }}">
                                            {{ Str::limit($post->user->name, config('app.truncate_name'), '...') }}
                                        </a>
                                        {{-- Display former name if applicable --}}
                                        @php
                                            $formerName = $post->user->getFormerNameIfApplicable(90);
                                        @endphp

                                        @if ($formerName)
                                            <span class="text-xs text-c-quiet">(Formerly:
                                                {{ Str::limit($formerName, config('app.truncate_name'), '...') }})</span>
                                        @endif
                                    </p>
                                @else
                                    {{-- no user found --}}
                                    <p class="mt-1 text-sm font-medium text-c-muted"
                                        title="{{ $post->username }}">
                                        {{ Str::limit($post->username, config('app.truncate_name'), '...') }}
                                    </p>
                                @endif

                                <p class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium leading-5 text-c-muted">
                                    @php
                                        $publishedAt = $post->published_at ?? $post->created_at;
                                    @endphp
                                    <span class="relative inline-flex" x-data="{ open: false }"
                                        @keydown.escape.window="open = false">
                                        <button type="button"
                                            class="cursor-pointer rounded-sm underline-offset-2 hover:text-c-primary hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-c-primary"
                                            @click="open = !open" :aria-expanded="open.toString()"
                                            aria-label="Show publish date">
                                            {{ $publishedAt->diffForHumans() }}
                                        </button>
                                        <span x-show="open" x-cloak @click.outside="open = false"
                                            x-transition.opacity.duration.120ms
                                            class="absolute left-0 top-full z-20 mt-1 w-max max-w-[80vw] rounded-md bg-c-primary px-2 py-1 text-[11px] font-semibold text-c-background shadow-lg">
                                            {{ $publishedAt->format('d.m.Y') }}
                                        </span>
                                    </span>
                                    @if ($post->comments->count() > 0)
                                        |
                                        <button type="button" class="cursor-pointer rounded-sm text-c-muted hover:text-c-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-c-primary md:cursor-auto"
                                            :class="{ 'underline': !$store.comments.visibility[{{ $post->id }}] }"
                                            @click="$store.comments.toggle({{ $post->id }})"
                                            :aria-expanded="$store.comments.visibility[{{ $post->id }}] ? 'true' : 'false'"
                                            aria-controls="comments-{{ $post->id }}">
                                            {{ $post->comments->count() }}
                                            comment{{ $post->comments->count() > 1 ? 's' : '' }}
                                            ♥</button>
                                    @endif
                                    <span>@include('components.gallery.share', ['post' => $post])</span>
                                    @if ($post->is_sensitive)
                                        <span class="inline-flex items-center gap-1 text-c-muted"
                                            title="Protected image">
                                            <x-icons.lock class="mx-1 h-3.5 w-3.5" aria-hidden="true" />
                                        </span>
                                    @endif
                                </p>
                            </div>
                            <!-- Add Comment Form (always visible) -->
                            <x-gallery.comment.add-comment-form :post="$post" />
                            <div id="comments-{{ $post->id }}" class="mt-4 max-h-64 space-y-4 overflow-auto px-3 text-sm leading-6 text-c-text sm:px-0 md:max-h-[420px] md:flex-grow"
                                x-show="$store.comments.visibility[{{ $post->id }}]">
                                <!-- Comments Section (conditionally visible) -->
                                @verified
                                    <x-gallery.comment.show-comments :post="$post" />
                                @else
                                    <div class="rounded-md border border-c-line/20 bg-c-surface-raised p-3 text-c-muted">
                                        <p>
                                            {{-- logged in but unverified: only old accounts from before FEAT-013,
                                                a pending registration is still a guest (see @verified in AppServiceProvider) --}}
                                            @auth
                                                <a class="rounded-sm text-sm text-c-primary underline hover:text-c-text focus:outline-none focus-visible:ring-2 focus-visible:ring-c-primary"
                                                    href="{{ route('verification.notice') }}">
                                                    Verify your email</a> to view and post comments.
                                            @else
                                                <a class="rounded-sm text-sm text-c-primary underline hover:text-c-text focus:outline-none focus-visible:ring-2 focus-visible:ring-c-primary"
                                                    href="{{ route('login') }}">
                                                    Log in</a> to view and post comments.
                                            @endauth
                                        </p>
                                    </div>
                                @endverified

                            </div>
                        </div>
                    </div>
                </main>
            </div>
        @endforeach {{-- posts as post --}}
    </div>
</div>
