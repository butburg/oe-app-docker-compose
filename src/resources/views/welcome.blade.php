
<x-app-layout>
    <x-slot name="header">
        <!-- Flex container with space between elements -->
        <div class="flex items-center justify-between">
            <x-header2>Oh Gallery</x-header2>
            <!-- Link to add a new post -->
            <a class="-my-3 rounded-md text-title-bg px-4 py-2  bg-title-btn-bg" href="{{ route('posts.create') }}">Create</a>
        </div>
    </x-slot>
    <div class="">
        <div class="mx-auto max-w-screen-2xl text-content-text bg-content-bg sm:px-6 lg:px-8">
            <!-- Include the Gallery Component -->
            @php
                $images = App\Models\Post::where('is_published', true)->get();
            @endphp
            <x-gallery.gallery :images="$images" />
        </div>
    </div>
    <footer class="text-content-text py-16 text-center text-sm">
        Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
    </footer>
</x-app-layout>
