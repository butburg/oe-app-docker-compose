<x-app-layout>
    <x-slot name="header">
        <!-- Flex container with space between elements -->
        <div class="flex items-center justify-between">
            <x-header2>Weedy Gallery</x-header2>

            {{-- check if there is a notif.success flash session --}}
            @if (Session::has('notif.success'))
                <div class="-my-3 rounded-lg bg-blue-300 px-4 py-2">
                    {{-- if it's there then print the notification --}}
                    <span class="italic text-white">{{ Session::get('notif.success') }}</span>
                </div>
            @endif

            <!-- Link to add a new post -->
            <x-create-post-button />

        </div>
    </x-slot>
    <div class="bg-c-background text-c-text">
        <div class="mx-auto max-w-screen-2xl sm:px-6 lg:px-8">
            <x-gallery.gallery :posts="$posts" />
            <!-- Pagination links -->
            <div class="mt-4">
                {{ $posts->links() }}
            </div>
        </div>

    </div>

</x-app-layout>
