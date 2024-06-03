<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Oh Gallery') }}
        </h2>
    </x-slot>

    <div class="">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Include the Gallery Component -->
            @php
                $images = App\Models\Post::where('is_published', true)->get();
            @endphp
            <x-gallery.gallery :images="$images" />

        </div>
    </div>
    <footer class="py-16 text-center text-sm text-black dark:text-green/70">
        Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
    </footer>
</x-app-layout>
