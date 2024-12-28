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

<div class="mx-auto py-6">
    <div class="{{-- grid grid-cols-1 lg:grid-cols-2 gap-4 --}}">
        @foreach ($posts as $post)
            <div id="post-{{ $post->id }}" x-data x-init="$store.comments.init({{ $post->id }});
            window.addEventListener('resize', () => $store.comments.updateOnResize({{ $post->id }}))">

                <main class="md:x-8 mx-1 mb-4 rounded-lg bg-c-primary/10 px-2 py-0 sm:mb-6 sm:px-6 md:mb-10">
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
                            <!-- Title and Author -->
                            <div class="flex flex-col rounded-lg bg-none">
                                <h1 class="text-content-bg break-words text-lg font-semibold md:text-2xl">
                                    {{ $post->title }}
                                </h1>

                                @if ($post->user)
                                    <p class="text-nav-bg text-sm font-medium" title="{{ $post->user->name }}">
                                        <a class="cursor-pointer hover:underline"
                                            href="{{ route('profile.show', $post->user) }}">
                                            {{ Str::limit($post->user->name, config('app.truncate_name'), '...') }}
                                        </a>
                                        {{-- Display former name if applicable --}}
                                        @php
                                            $formerName = $post->user->getFormerNameIfApplicable(90);
                                        @endphp

                                        @if ($formerName)
                                            <span class="text-xs text-gray-500">(Formerly:
                                                {{ Str::limit($formerName, config('app.truncate_name'), '...') }})</span>
                                        @endif
                                    </p>
                                @else
                                    {{-- no user found --}}
                                    <p class="text-nav-bg text-sm font-medium text-gray-500"
                                        title="{{ $post->username }}">
                                        {{ Str::limit($post->username, config('app.truncate_name'), '...') }}
                                    </p>
                                @endif

                                <p class="mt-2 text-xs font-medium leading-4 opacity-60">
                                    <span title="Last update {{ $post->updated_at->diffForHumans() }}">
                                        {{ $post->published_at->diffForHumans() }}
                                    </span>
                                    @if ($post->comments()->count() > 0)
                                        |
                                        <span class="cursor-pointer md:cursor-auto"
                                            :class="{ 'underline': !$store.comments.visibility[{{ $post->id }}] }"
                                            @click="$store.comments.toggle({{ $post->id }})">
                                            {{ $post->comments()->count() }}
                                            comment{{ $post->comments()->count() > 1 ? 's' : '' }}
                                            ♥</span>
                                    @endif
                                    | @include('components.gallery.share', ['post' => $post])
                                </p>
                            </div>
                            <!-- Add Comment Form (always visible) -->
                            <x-gallery.comment.add-comment-form :post="$post" />
                            <div class="text-title-bg mt-4 max-h-64 space-y-4 overflow-auto px-3 text-sm leading-6 sm:px-0 md:max-h-[420px] md:flex-grow"
                                x-init="scrollToBottom" x-show="$store.comments.visibility[{{ $post->id }}]">
                                <!-- Comments Section (conditionally visible) -->
                                @auth
                                    <x-gallery.comment.show-comments :post="$post" />
                                @else
                                    <div class="text-comment-text rounded-lg bg-c-primary/40 p-3">
                                        <p>
                                            <a class="hover:text-nav-text text-primary rounded-md text-sm underline focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                                href="{{ route('login') }}">
                                                Log in</a> to view and post comments.
                                        </p>
                                    </div>
                                @endauth

                            </div>
                        </div>
                    </div>
                </main>
            </div>
        @endforeach {{-- posts as post --}}
    </div>
</div>
