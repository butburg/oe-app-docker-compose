<div class="mx-auto py-6">
    <div class="{{-- grid grid-cols-1 lg:grid-cols-2 gap-4 --}}">
        @foreach ($posts->sortByDesc('created_at') as $post)
            <div id="post-{{ $post->id }}" x-data="{ showComments: false }">
                <main
                    class="md:x-8 mx-1 mb-4 rounded-lg bg-c-primary/10 px-2 py-6 sm:mb-6 sm:px-6 md:mb-10">
                    <div
                        class="mx-auto grid grid-cols-1 md:grid-cols-3 md:gap-x-10">
                        <!-- Image -->
                        <div class="col-span-3 flex cursor-pointer items-center justify-center md:col-span-2 md:cursor-auto"
                            @click="showComments = !showComments">
                            @include(
                                'components.image_or_placeholder',
                                [
                                    'image' => $post->image,
                                    'size_type' => 'l', // Beispiel für einen Bildtyp
                                    'alt_title' =>
                                        'Show comments for ' .
                                        $post->title,
                                    'style' => '',
                                ]
                            )
                        </div>
                        <!-- Text and Comments -->
                        <div
                            class="relative col-span-3 flex flex-col pt-3 md:col-span-1 md:pt-0">
                            <!-- Title and Author -->
                            <div class="flex flex-col rounded-lg bg-none">
                                <h1
                                    class="text-content-bg break-words text-lg font-semibold md:text-2xl">
                                    {{ $post->title }}
                                </h1>

                                @if ($post->user)
                                    <p class="text-nav-bg text-sm font-medium"
                                        title="{{ $post->user->name }}">
                                        <a class="cursor-pointer hover:underline"
                                            href="{{ route('profile.show', $post->user) }}">
                                            {{ Str::limit($post->user->name, config('app.truncate_name'), '...') }}
                                        </a>
                                        {{-- Display former name if applicable --}}
                                        @php
                                            $formerName = $post->user->getFormerNameIfApplicable(
                                                90,
                                            );
                                        @endphp

                                        @if ($formerName)
                                            <span
                                                class="text-xs text-gray-500">(Formerly:
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

                                <p
                                    class="mt-2 text-xs font-medium leading-4 opacity-60">
                                    <span
                                        title="Last update {{ $post->updated_at->diffForHumans() }}">
                                        {{ $post->created_at->diffForHumans() }}
                                    </span>
                                    @if ($post->comments()->count() > 0)
                                        <span
                                            class="cursor-pointer md:cursor-auto"
                                            @click="showComments = !showComments">|
                                            {{ $post->comments()->count() }}
                                            comment{{ $post->comments()->count() > 1 ? 's' : '' }}
                                            ♥</span>
                                    @endif
                                    | @include(
                                        'components.gallery.share',
                                        ['post' => $post]
                                    )
                                </p>
                            </div>
                            <div class="text-title-bg mt-4 max-h-64 space-y-4 overflow-auto px-3 text-sm leading-6 sm:px-0 md:max-h-[420px] md:flex-grow"
                                x-data="{ scrollToBottom() { this.$el.scrollTop = this.$el.scrollHeight } }" x-init="scrollToBottom"
                                x-show="showComments || window.innerWidth >= 768"
                                @click.away="showComments = false">
                                <!-- Comments Section (conditionally visible) -->
                                <x-gallery.comment.show-comments
                                    :post="$post" />
                                <!-- Add Comment Form (always visible) -->
                                <x-gallery.comment.add-comment-form
                                    :post="$post" />
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        @endforeach {{-- posts as post --}}
    </div>
</div>
