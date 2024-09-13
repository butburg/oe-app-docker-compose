{{-- we are using AppLayout Component located in app\View\Components\AppLayout.php which use
resources\views\layouts\app.blade.php view --}}
<x-app-layout>
    <!-- Define a slot named "header" -->
    <x-slot name="header">
        <!-- Flex container with space between elements -->
        <div class="flex items-center justify-between">
            <!-- Title for the page -->
            <x-header2>Your Posts</x-header2>
            {{-- check if there is a notif.success flash session --}}
            @if (Session::has('notif.success'))
                <div class="-my-3 rounded-lg bg-blue-300 px-4 py-2">
                    <span
                        class="italic text-white">{{ Session::get('notif.success') }}</span>
                </div>
            @endif
            <!-- Link to add a new post -->
            <x-create-post-button />
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl py-6 text-c-text sm:px-6 lg:px-8">
        <!-- Drafts -->

        <div
            class="mb-4 overflow-hidden bg-c-primary/20 p-6 shadow-sm sm:rounded-lg">

            <!-- Title for draft posts -->
            <h3 class="mb-4 text-lg font-semibold leading-tight">Not yet
                published</h3>

            <!-- Box with Infos -->
            <div class="text-md mb-4 flex rounded-lg bg-c-background p-4 text-blue-300"
                role="alert">
                <svg class="me-3 mt-[2px] inline h-4 w-4 flex-shrink-0"
                    aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <span class="sr-only">Info</span>
                <div>
                    <span class="font-medium">Important Information Before You
                        Publish:</span>
                    <ul class="mt-1.5 list-inside list-disc">
                        <li>Once published, your post <span
                                class="font-semibold">cannot be edited</span>.
                        </li>
                        <li>You have the option to <span
                                class="font-semibold">hide</span> or <span
                                class="font-semibold">delete</span> your post at
                            any time.</li>
                        <li>Deleting a post will also remove all associated
                            comments.</li>
                        <li>If you need to make urgent changes to a published
                            post, please contact an admin.</li>
                    </ul>

                    <p class="mt-2"><span class=""></span>🔒 <span
                            class="font-semibold">Think carefully before
                            you publish!</span> Make
                        sure everything is exactly how you want it, as you won’t
                        be able to make changes after it goes
                        live.</p>
                </div>
            </div>

            <!-- Pagination links top -->
            <div class="mt-4">
                {{ $draftPosts->links() }}
            </div>

            <!-- Section to display draft posts as flex-->
            @forelse ($draftPosts as $post)
                <div
                    class="flex flex-col items-start border-b py-4 md:flex-row md:items-center">
                    <!-- draft Image -->

                    <div class="flex flex-grow">
                        <div class="flex-shrink-0">
                            <a
                                href="{{ $post->image ? Storage::url($post->image->variants->firstWhere('size_type', 'xl')->path) : '#' }}">
                                <x-image_or_placeholder
                                    alt_title="Open large image"
                                    style="h-24 w-24 rounded-full object-cover"
                                    :image="$post->image" size_type="l" />
                            </a>
                        </div>
                        <!-- Display draft details -->
                        <div class="ml-4">
                            <span
                                class="text-lg font-medium">{{ $post->title }}</span>

                            <small class="block text-gray-300">Created:
                                {{ $post->created_at->diffForHumans() }}</small>

                            @if ($post->created_at->diffForHumans() !== $post->updated_at->diffForHumans())
                                <small class="block">Last update:
                                    {{ $post->updated_at->diffForHumans() }}</small>
                            @endif

                            <small class="block">
                                @if ($post->image)
                                    <a
                                        href="{{ Storage::url($post->image->variants->firstWhere('size_type', 'xl')->path) }}">Open
                                        full screen</a>
                                @else
                                    No image found (ID:{{ $post->id }})
                                @endif
                            </small>

                        </div>
                    </div>

                    <!-- Actions for the draft -->
                    <div class="mt-4 flex flex-row gap-3">
                        <!-- Link to publish post -->
                        <a class="flex items-center gap-1 rounded-md border border-l-4 border-green-500 px-4 py-2 hover:bg-green-500 hover:text-white"
                            href="{{ route('posts.publish', $post->id) }}">
                            PUBLISH <span>▽</span>
                        </a>
                        <!-- Link to edit post -->
                        <a class="flex items-center gap-1 rounded-md border border-l-4 border-yellow-500 px-4 py-2 hover:bg-yellow-500 hover:text-white"
                            href="{{ route('posts.edit', $post->id) }}">EDIT
                            <span>✎</span></a>

                        <!-- Form to delete draft post -->
                        <form method="post"
                            action="{{ route('posts.destroy', $post->id) }}">
                            @csrf
                            @method('delete')
                            <button
                                class="flex items-center gap-1 rounded-md border border-l-4 border-red-500 px-4 py-2 hover:bg-red-500 hover:text-white"
                                type="submit"
                                onclick="return confirm('Are you sure you want to delete this post?')">
                                DELETE <span>✕</span>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <a class="-my-3 rounded-lg border-2 border-c-accent/80 bg-c-accent/80 px-2 py-1 text-c-background hover:bg-c-accent active:border-c-primary"
                    href="{{ route('posts.create') }}">Create a new post!</a>
                <div class="border-b border-slate-100 p-4">
                    <small>No draft posts found.</small>
                </div>
            @endforelse
            <!-- Pagination links bottom -->
            <div class="mt-4">
                {{ $draftPosts->links() }}
            </div>
        </div>

        <!-- Published -->
        <div
            class="overflow-hidden bg-c-primary/20 p-6 shadow-sm sm:rounded-lg">
            <!-- Title for published posts -->
            <h3 class="mb-4 text-lg font-semibold leading-tight">Published</h3>

            <!-- Pagination links top -->
            <div class="mt-4">
                {{ $publishedPosts->links() }}
            </div>

            <!-- Section to display published posts as flex-->
            @forelse ($publishedPosts as $post)
                <div
                    class="flex flex-col items-start border-b py-4 md:flex-row md:items-center">
                    <!-- Published Image -->
                    <div class="flex flex-grow">

                        @php
                            $page = app(
                                'App\Http\Controllers\PostController',
                            )->getPageForPost(
                                $post,
                                config('app.posts_per_page'),
                            );
                        @endphp

                        <div
                            class="@if (!$post->is_published) opacity-25 @endif flex-shrink-0">

                            <a class="block transform transition-transform hover:scale-105"
                                href="{{ route('dashboard') }}?page={{ $page }}#post-{{ $post->id }}">
                                <x-image_or_placeholder
                                    alt_title="Open large image"
                                    style="h-24 w-24 rounded-full object-cover"
                                    :image="$post->image" size_type="s" />
                            </a>
                        </div>
                        <!-- Display Published details -->
                        <div class="ml-4">
                            <a class="text-lg font-medium hover:underline"
                                href="{{ route('dashboard') }}?page={{ $page }}#post-{{ $post->id }}">
                                {{ $post->title }}</a>

                            @if (!$post->is_published)
                                <span
                                    class="font-semibold text-red-500">(HIDDEN)</span>
                            @endif

                            <small class="block text-gray-300">Created:
                                {{ $post->created_at->diffForHumans() }}</small>

                            @if ($post->created_at->diffForHumans() !== $post->updated_at->diffForHumans())
                                <small>Last update:
                                    {{ $post->updated_at->diffForHumans() }}</small>
                            @endif

                            <small class="block text-gray-300">Comments:
                                {{ $post->comments->count() }}</small>

                            <small class="block">
                                @if ($post->image)
                                    <a
                                        href="{{ Storage::url($post->image->variants->firstWhere('size_type', 'xl')->path) }}">Open
                                        full screen</a>
                                @else
                                    No image found (ID:{{ $post->id }})
                                @endif
                            </small>

                        </div>
                    </div>

                    <!-- Actions for the published post -->
                    <div class="mt-4 flex flex-row gap-3">
                        <!-- Admin can edit the post -->
                        @if ($isAdmin)
                            <a class="flex items-center gap-1 rounded-md border border-l-4 border-yellow-500 px-4 py-2 shadow hover:bg-yellow-500 hover:text-white"
                                href="{{ route('posts.edit', $post->id) }}">EDIT
                                <span>✎</span></a>
                        @endif

                        @if ($post->is_published)
                            <!-- Link to hide post -->
                            <a class="flex items-center gap-1 rounded-md border border-l-4 border-yellow-500 px-4 py-2 hover:bg-yellow-500 hover:text-white"
                                href="{{ route('posts.hide', $post->id) }}"
                                title="The image can't be seen in the gallery anymore.">
                                <span class="px-2.5">HIDE</span> ◐
                            </a>
                        @else
                            <!-- Link to unhide post -->
                            <a class="flex items-center gap-1 rounded-md border border-l-4 border-green-500 px-4 py-2 hover:bg-green-500 hover:text-white"
                                href="{{ route('posts.publish', $post->id) }}"
                                title="Put the image back into the gallery.">
                                UNHIDE <span>◑</span>
                            </a>
                        @endif

                        <!-- Form to delete post -->
                        <form method="post"
                            action="{{ route('posts.destroy', $post->id) }}">
                            @csrf
                            @method('delete')
                            <button
                                class="flex items-center gap-1 rounded-md border border-l-4 border-red-500 px-4 py-2 hover:bg-red-500 hover:text-white"
                                type="submit"
                                onclick="return confirm('Are you sure you want to delete this post?')">
                                DELETE <span>✕</span>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="border-b border-slate-100 p-4">
                    <small>No published posts found. Create a post and <span
                            class="font-semibold">publish</span>
                        it!</small>
                </div>
            @endforelse
            <!-- Pagination links bottom -->
            <div class="mt-4">
                {{ $publishedPosts->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
