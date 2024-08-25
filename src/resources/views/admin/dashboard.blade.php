<x-app-layout>
    <x-slot name="header">
        <x-header2>Admin Dashboard</x-header2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 pb-2 sm:px-6 lg:px-8">
            <div class="bg-c-primary/10 p-4 text-c-text shadow sm:rounded-lg sm:p-8">
                <div class="max-w-full">
                    <!-- Link to show all posts -->
                    <a class="-my-3 rounded-lg border-2 border-c-accent/80 bg-c-accent/80 px-3 py-1 text-c-background hover:bg-c-accent active:border-c-primary"
                        href="{{ route('posts.index') }}">All posts</a>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="bg-c-primary/10 p-4 text-c-text shadow sm:rounded-lg sm:p-8">
                <div class="max-w-full">
                    @include('admin.partials.user-list')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
