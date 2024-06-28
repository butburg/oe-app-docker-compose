<x-app-layout>
    <x-slot name="header">
        <!-- Flex container with space between elements -->
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Oh Gallery') }}
            </h2>
            <!-- Link to add a new post -->
            <a class="-my-3 rounded-md bg-blue-500 px-4 py-2 text-white" href="{{ route('posts.create') }}">Create</a>
        </div>
    </x-slot>
    <div class="">
        <div class="mx-auto max-w-screen-2xl bg-gray-200 sm:px-6 lg:px-8">
            <!-- Include the Gallery Component -->
            @php
                $images = App\Models\Post::where('is_published', true)->get();
            @endphp
            <x-gallery.gallery :images="$images" />
        </div>
    </div>
    <footer class="dark:text-green/70 py-16 text-center text-sm text-black">
        Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
    </footer>
</x-app-layout>
