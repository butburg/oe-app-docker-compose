<x-app-layout>
    <x-slot name="header">
        <!-- Flex container with space between elements -->
        <div class="flex items-center justify-between">
            <x-header2>Oh Gallery</x-header2>
            <!-- Link to add a new post -->
            <a class="-my-3 rounded-md bg-c-accent/80 px-4 py-2 text-c-background hover:bg-c-accent"
                href="{{ route('posts.create') }}">Create</a>
        </div>
    </x-slot>
    <div class="bg-c-background text-c-text">
        <div class="mx-auto max-w-screen-2xl sm:px-6 lg:px-8">
            <!-- Include the Gallery Component -->
            @php
                $images = App\Models\Post::where('is_published', true)->get();
            @endphp
            <x-gallery.gallery :images="$images" />
        </div>
    </div>
    <footer class="text-content-text py-16 text-center text-sm">
        Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
        @if (app()->environment('local'))
            <br>
            Laravel: {{ app()->version() }}<br>
            PHP {{ PHP_VERSION }}<br>
            Composer: {{ shell_exec('composer --version | grep -o -E "[0-9]+\.[0-9]+\.[0-9]+"') }}<br>
            npm: {{ shell_exec('npm --version | grep -o -E "[0-9]+\.[0-9]+\.[0-9]+"') }}<br>
            Vite: {{ shell_exec('npm show vite version | grep -o -E "[0-9]+\.[0-9]+\.[0-9]+"') }}<br>
            SQLite: {{ shell_exec('sqlite3 --version | grep -o -E "[0-9]+\.[0-9]+\.[0-9]+"') }}
        @endif
    </footer>
</x-app-layout>
