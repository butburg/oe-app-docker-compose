<x-app-layout>
    <x-slot name="header">
        <x-header2>Profile from {{ $user->name }}</x-header2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- User Info Section -->
            <div class="mb-6 overflow-hidden bg-c-primary/10 p-6 shadow-sm sm:rounded-lg">
                <div class="flex flex-wrap justify-center gap-6">

                    <!-- Profile Image -->
                    @php
                        $isOnline = $user->session ? 'border-4 border-green-500' : '';
                    @endphp
                    @include('components.image_or_placeholder', [
                        'image' => $user->image,
                        'size_type' => 'l',
                        'alt_title' => 'Your Profile Image',
                        'style' => "{$isOnline} h-80 w-80 my-2 rounded-full box-border object-cover bg-gray-200",
                        'placeholder' => 'storage/files/images/starfish.svg',
                    ])

                    <div class="max-w-80 flex flex-col justify-center gap-2">
                        <!-- User Name and Former Name Below Image and Stats -->
                        @include('components.profile.user-name', [
                            'user' => $user,
                        ])

                        <!-- User Stats -->
                        @include('components.profile.user-stats', [
                            'user' => $user,
                        ])

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

                        <div class="text-sm text-gray-400">
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
            <div class="posts-container overflow-hidden bg-c-primary/10 p-6 text-c-text shadow-sm sm:rounded-lg">
                <h3 class="mb-4 truncate text-lg font-bold">Posts released by
                    {{ $user->name }}
                </h3>
                <ul class="flex flex-wrap gap-x-8">
                    @forelse ($posts as $post)
                        @php
                            $page = app('App\Http\Controllers\PostController')->getPageForPost($post);
                        @endphp
                        <li class="mb-6  bg-c-primary/40 p-1 rounded-lg w-72">
                            <a class="text-c-primary"
                                href="{{ route('dashboard') }}?page={{ $page }}#post-{{ $post->id }}">
                                <div class="flex gap-4 items-center h-full">
                                    <!-- Post Image -->
                                    <x-image_or_placeholder style="h-16 w-16 rounded-md object-cover" :image="$post->image"
                                        :alt_title="$post->title" size_type="s" />
                                    <div>
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
