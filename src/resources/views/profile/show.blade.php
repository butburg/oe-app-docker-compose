<x-app-layout>
    <x-slot name="header">
        <x-header2>Profile from {{ $user->name }}</x-header2>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- User Info Section -->
            <div class="app-surface mb-4 p-4 sm:p-6">
                <div class="flex flex-col items-center gap-4 sm:flex-row sm:gap-6">

                    <!-- Profile Image -->
                    @php
                        $isOnline = $user->session ? 'border-2 border-c-accent' : 'border-2 border-c-line';
                    @endphp
                    @include('components.image_or_placeholder', [
                        'image' => $user->image,
                        'size_type' => 'l',
                        'alt_title' => 'Your Profile Image',
                        'style' => "{$isOnline} my-2 h-80 w-80 shrink-0 rounded-full object-cover bg-c-surface-raised",
                        'placeholder' => 'images/starfish.svg',
                    ])

                    <div class="min-w-0 max-w-2xl text-center sm:text-left">
                        <!-- User Name and Former Name Below Image and Stats -->
                        @include('components.profile.user-name', [
                            'user' => $user,
                        ])

                        <!-- User Stats -->
                        <div class="flex justify-center sm:justify-start">
                            @include('components.profile.user-stats', [
                                'user' => $user,
                            ])
                        </div>

                        @include('profile.partials.display-description', ['user' => $user])

                        <!-- Display "Last seen" information only -->
                        @php
                            // max needs timestamps, so get these compare and than decide which time object to choose
                            $userUpdatedAt = $user->updated_at;

                            $lastUpdatedPost = $user->posts()->latest('updated_at')->first();
                            $postUpdatedAt = $lastUpdatedPost ? $lastUpdatedPost->updated_at : null;

                            // Determine the most recent updated_at timestamp
                            $mostRecentUpdatedAt = $postUpdatedAt
                                ? max($userUpdatedAt, $postUpdatedAt)
                                : $userUpdatedAt;
                        @endphp

                        <div class="mt-3 text-sm text-c-muted">
                            <p>
                                <strong>Seen:</strong>
                                @if ($user->session)
                                    @php
                                        // for showing time if seesion active
                                        $lastActivity = \Carbon\Carbon::parse($user->session->last_activity);
                                    @endphp
                                    @if ($lastActivity->diffInMinutes() < 3)
                                        Active
                                    @else
                                        {{ $lastActivity->diffForHumans() }}
                                    @endif
                                @else
                                    {{ $mostRecentUpdatedAt->format('d.m.y') }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Posts Section -->
            <div class="posts-container app-surface overflow-hidden p-4 text-c-text sm:p-6">
                <h3 class="mb-4 break-words text-lg font-semibold leading-snug">Posts released by
                    {{ $user->name }}
                </h3>
                <ul class="flex flex-wrap gap-4">
                    @forelse ($posts as $post)
                        @php
                            $page = app('App\Http\Controllers\PostController')->getPageForPost($post);
                        @endphp
                        <li class="app-surface-raised w-full max-w-72 p-2 sm:w-72">
                            <a class="block h-full min-w-0 text-c-text transition-colors hover:text-c-primary"
                                href="{{ route('dashboard') }}?page={{ $page }}#post-{{ $post->id }}">
                                <div class="flex h-full items-center gap-3">
                                    <!-- Post Image -->
                                    <x-image_or_placeholder style="h-16 w-16 shrink-0 rounded-md bg-c-background object-contain" :image="$post->image"
                                        :alt_title="$post->title" size_type="s" />
                                    <div class="min-w-0 flex-1 break-words">
                                        <!-- Construct the URL with the page query parameter and the post anchor -->
                                        {{ $post->title }}

                                        <p>{{ $post->published_at->format('d.m.y') }} {{-- , H:i --}}
                                        </p>
                                        <!-- Comments Count -->
                                        <p class="text-sm">Comments:
                                            {{ $post->comments_count }}</p>
                                    </div>
                                </div>
                            </a>
                        </li>
                    @empty
                        No posts. Just stalking.
                    @endforelse
                </ul>
                <!-- Pagination Links -->
                <div class="mt-4">
                    {{ $posts->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
