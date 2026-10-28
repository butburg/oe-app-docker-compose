<x-app-layout>
    <x-slot name="header">
        <x-header2>{{ config('app.name') }}</x-header2>
    </x-slot>

    <div class="bg-c-background text-c-text">
        <div class="mx-auto max-w-screen-2xl sm:px-6 lg:px-8">
            <x-gallery.gallery :posts="$posts" />
            @if ($posts->isEmpty())
                <div class="app-surface mt-4 p-5 text-sm text-c-muted" role="status">
                    @if ($search !== '')
                        No gallery posts match “{{ $search }}”.
                    @else
                        No public posts have been shared yet.
                    @endif
                </div>
            @endif
            <!-- Pagination links -->
            <div class="mt-4">
                {{ $posts->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
