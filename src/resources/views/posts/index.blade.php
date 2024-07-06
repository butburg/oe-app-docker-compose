{{-- we are using AppLayout Component located in app\View\Components\AppLayout.php which use resources\views\layouts\app.blade.php view --}}
<x-app-layout>
    <!-- Define a slot named "header" -->
    <x-slot name="header">
        <!-- Flex container with space between elements -->
        <div class="flex items-center justify-between">
            <!-- Title for the page -->
            <x-header2>Posts</x-header2>
            <!-- Link to add a new post -->
            <a class="-my-3 rounded-md bg-c-accent px-4 py-2 text-black" href="{{ route('posts.create') }}">Create</a>
        </div>
    </x-slot>

    <div class="py-12 text-c-text">
        <div class="mx-auto mb-4 max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-c-primary/20 shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Title for draft posts -->
                    <h3 class="mb-4 text-lg font-semibold leading-tight">Not yet published</h3>
                    <!-- Table to display draft posts -->
                    <table class="w-full table-auto border-collapse text-sm">
                        <thead>
                            <tr>
                                <!-- Table header for post and action -->
                                <th class="400 border-b p-4 pb-3 pl-8 pt-0 text-left font-medium">Image</th>
                                <th class="400 border-b p-4 pb-3 pl-8 pt-0 text-left font-medium">Details
                                </th>
                                <th class="400 border-b p-4 pb-3 pl-8 pt-0 text-left font-medium">Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-100">
                            {{-- Loop through draft posts --}}
                            @forelse ($draftPosts as $post)
                                <tr>
                                    <td class="dark:400 border-b border-slate-100 py-4 dark:border-slate-700">
                                        <!-- Post Image -->
                                        <img class="h-24 w-24 rounded-full object-cover"
                                            src="{{ asset('storage/' . $post->image_file) }}">
                                    </td>

                                    <!-- Display post details -->
                                    <td class="dark:400 border-b border-slate-100 p-4 pl-8 dark:border-slate-700">

                                        <!-- Post content -->
                                        <span>
                                            {{ $post->title }}
                                        </span>
                                        <!-- Display info file if exists -->
                                        @isset($post->image_file)
                                            <span>
                                                <small> | <a href="{{ Storage::url($post->image_file) }}" class="hover:underline">Open
                                                        File</a></small>
                                            </span>
                                        @endisset
                                        <!-- Display last update time -->
                                        <span>
                                            <small>{{ ' | ' . $post->updated_at->diffForHumans() }}</small>
                                        </span>
                                    </td>
                                    <!-- Actions for the post -->
                                    <td class="dark:400 border-b border-slate-100 p-4 pl-8 dark:border-slate-700">
                                        <!-- Link to publish post -->
                                        <a class="rounded-md border border-green-500 px-4 py-2 hover:bg-green-500 hover:text-white"
                                            href="{{ route('posts.publish', $post->id) }}">PUBLISH ▽</a>
                                        <!-- Link to edit post -->
                                        <a class="rounded-md border border-yellow-500 px-4 py-2 hover:bg-yellow-500 hover:text-white"
                                            href="{{ route('posts.edit', $post->id) }}">EDIT</a>
                                        <!-- Form to delete post -->
                                        <form class="inline" method="post"
                                            action="{{ route('posts.destroy', $post->id) }}">
                                            @csrf
                                            @method('delete')
                                            <button
                                                class="relative top-[1px] h-[35px] rounded-md border border-red-500 px-4 py-2 hover:bg-red-500 hover:text-white"
                                                type="submit">DELETE</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <!-- Display message if no draft posts -->
                                <tr>
                                    <td class="dark:400 border-b border-slate-100 p-4 pl-8 dark:border-slate-700"
                                        colspan="2">
                                        No draft posts found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-c-primary/20 shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Title for published posts -->
                    <h3 class="mb-4 text-lg font-semibold leading-tight">Published</h3>
                    <!-- Table to display published posts -->
                    <table class="w-full table-auto border-collapse text-sm">
                        <thead>
                            <tr>
                                <th class="border-b p-4 pb-3 pl-8 pt-0 text-left font-medium">Image</th>
                                <th class="border-b p-4 pb-3 pl-8 pt-0 text-left font-medium">Details
                                </th>
                                <th class="border-b p-4 pb-3 pl-8 pt-0 text-left font-medium">Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-100">
                            {{-- populate our published post data --}}
                            @forelse ($publishedPosts as $post)
                                <tr>
                                    <td class="py-4">
                                        <!-- Post Image -->
                                        <img class="h-24 w-24 rounded-full object-cover"
                                            src="{{ asset('storage/' . $post->image_file) }}">
                                    </td>
                                    <td
                                        class="dark:400 items-center justify-center border-b border-slate-100 p-4 pl-8 dark:border-slate-700">
                                        <!-- Post content -->
                                        <span>
                                            {{ $post->title }}
                                        </span>
                                        <!-- Display info file if exists -->
                                        @isset($post->image_file)
                                            <span>
                                                <small> | <a href="{{ Storage::url($post->image_file) }}" class="hover:underline">Open
                                                        File</a></small>
                                            </span>
                                        @endisset
                                        <!-- Display last update time -->
                                        <span>
                                            <small>{{ ' | ' . $post->updated_at->diffForHumans() }}</small>
                                        </span>
                                    </td>
                                    <td
                                        class="dark:400 items-center justify-center border-b border-slate-100 p-4 pl-8 dark:border-slate-700">
                                        <!-- Link to publish post -->
                                        <a class="rounded-md border border-red-500 px-4 py-2 hover:bg-red-500 hover:text-white"
                                            href="{{ route('posts.make-draft', $post->id) }}"
                                            title="Pull back the image to not yet published.">WITHDRAW △</a>
                                    </td>
                                </tr>
                            @empty
                                <!-- Display message if no published posts -->
                                <tr>
                                    <td class="dark:400 border-b border-slate-100 p-4 pl-8 dark:border-slate-700"
                                        colspan="2">
                                        No published posts found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
