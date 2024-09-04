<x-app-layout>
    <x-slot name="header">
        <x-header2>Profile from {{ $user->name }}</x-header2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- User Info Section -->
            <div
                class="mb-6 overflow-hidden bg-c-primary/10 p-6 shadow-sm sm:rounded-lg">

                <!-- Image and Stats Side by Side -->
                <div class="flex items-center gap-6">
                    <!-- Profile Image -->
                    @php
                        $isOnline = $user->session
                            ? 'border-4 border-green-500'
                            : '';
                    @endphp

                    @include('components.image_or_placeholder', [
                        'image' => $user->image,
                        'size_type' => 'l',
                        'alt_title' => 'Your Profile Image',
                        'style' => "{$isOnline} h-80 w-80 my-2 rounded-full box-border bg-gray-200",
                        'placeholder' =>
                            'storage/files/images/starfish.svg',
                    ])
                    <!-- User Stats -->
                    @include('components.profile.user-stats', [
                        'user' => $user,
                    ])
                </div>

                <!-- User Name and Former Name Below Image and Stats -->
                @include('components.profile.user-name', [
                    'user' => $user,
                ])

                <!-- Display "Seen" information only -->
                <div class="text-sm text-gray-400">
                    <p>
                        <strong>Seen:</strong>
                        @if ($user->session)
                            {{ \Carbon\Carbon::parse($user->session->last_activity)->diffForHumans() }}
                        @else
                            Offline
                        @endif
                    </p>
                </div>
            </div>
            <!-- User Posts Section -->
            <div
                class="posts-container overflow-hidden bg-c-primary/10 p-6 text-c-text shadow-sm sm:rounded-lg">
                <h3 class="mb-4 text-lg font-bold">Posts by {{ $user->name }}
                </h3>
                <ul class="flex flex-wrap gap-x-8">
                    @forelse ($posts as $post)
                        @php
                            $page = app(
                                'App\Http\Controllers\PostController',
                            )->getPageForPost(
                                $post,
                                config('app.posts_per_page'),
                            );
                        @endphp
                        <li class="mb-6 flex gap-4">
                            <!-- Post Image -->
                            <x-image_or_placeholder
                                style="h-16 w-16 rounded-md object-cover"
                                :image="$post->image" :alt_title="$post->title"
                                size_type="s" />
                            <div>
                                <!-- Construct the URL with the page query parameter and the post anchor -->
                                <a class="text-blue-500"
                                    href="{{ route('dashboard') }}?page={{ $page }}#post-{{ $post->id }}">
                                    {{ $post->title }}
                                </a>
                                <p>{{ $post->created_at->format('d.m.y, H:i') }}
                                </p>
                                <!-- Comments Count -->
                                <p class="text-sm text-gray-500">Comments:
                                    {{ $post->comments_count }}</p>
                            </div>
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
