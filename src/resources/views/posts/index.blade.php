{{-- we are using AppLayout Component located in app\View\Components\AppLayout.php which use resources\views\layouts\app.blade.php view --}}
<x-app-layout>
    <!-- Define a slot named "header" -->
    <x-slot name="header">
        <!-- Flex container with space between elements -->
        <div class="flex justify-between items-center">
            <!-- Title for the page -->
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ 'Posts' }} <!-- Static title -->
            </h2>
            <!-- Link to add a new post -->
            <a href="{{ route('posts.create') }}" class="bg-blue-500 text-white px-4 py-2 -my-3 rounded-md">Create</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-4">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                     <!-- Title for draft posts -->
                    <h3 class="font-semibold text-lg text-gray-800 leading-tight mb-4">Draft</h3>
                    <!-- Table to display draft posts -->
                    <table class="border-collapse table-auto w-full text-sm">
                        <thead>
                            <tr>
                                <!-- Table header for post and action -->
                                <th class="border-b font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 text-left">Post</th>
                                <th class="border-b font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            {{-- Loop through draft posts --}}
                            @forelse ($draftPosts as $post)
                            <tr>
                                <!-- Display post content -->
                                <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">
                                    <a class="mr-1 text-lg" href="{{ route('posts.publish', $post->id) }}">
                                        🔲
                                    </a>
                                    <!-- Post content -->
                                    <span>
                                        {{ $post->title }}
                                    </span>
                                    <!-- Display info file if exists -->
                                    @isset ($post->image_file)
                                    <span>
                                        <small> | <a href="{{ Storage::url($post->image_file) }}">Open File</a></small>
                                    </span>
                                    @endisset
                                    <!-- Display last update time -->
                                    <span>
                                        <small>{{ ' | ' . $post->updated_at->diffForHumans() }}</small>
                                    </span>
                                </td>
                                <!-- Actions for the post -->
                                <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">
                                    <!-- Link to edit post -->
                                    <a href="{{ route('posts.edit', $post->id) }}" class="border border-yellow-500 hover:bg-yellow-500 hover:text-white px-4 py-2 rounded-md">EDIT</a>
                                    <!-- Form to delete post -->
                                    <form method="post" action="{{ route('posts.destroy', $post->id) }}" class="inline">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="border border-red-500 hover:bg-red-500 hover:text-white px-4 py-2 rounded-md h-[35px] relative top-[1px]">DELETE</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <!-- Display message if no draft posts -->
                            <tr>
                                <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400" colspan="2">
                                    No draft posts found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                     <!-- Title for published posts -->
                    <h3 class="font-semibold text-lg text-gray-800 leading-tight mb-4">Published</h3>
                    <!-- Table to display published posts -->
                    <table class="border-collapse table-auto w-full text-sm">
                        <thead>
                            <tr>
                                <th class="border-b font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 text-left">Post</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            {{-- populate our published post data --}}
                            @forelse ($publishedPosts as $post)
                            <tr>
                                <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400 justify-center items-center">
                                    <!-- Display checkmark to mark as published -->
                                    <a class="mr-1 text-lg" href="{{ route('posts.make-draft', $post->id) }}">
                                        ✅
                                    </a>
                                    <!-- Post content -->
                                    <span>
                                        {{ $post->title }}
                                    </span>
                                    <!-- Display info file if exists -->
                                    @isset ($post->image_file)
                                    <span>
                                        <small> | <a href="{{ Storage::url($post->image_file) }}">Open File</a></small>
                                    </span>
                                    @endisset
                                    <!-- Display last update time -->
                                    <span>
                                        <small>{{ ' | ' . $post->updated_at->diffForHumans() }}</small>
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <!-- Display message if no published posts -->
                            <tr>
                                <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400" colspan="2">
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
