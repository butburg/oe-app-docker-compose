
<x-app-layout>
    <x-slot name="header">
        <x-header2>Admin Dashboard</x-header2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 pb-2">
            <div class="p-4 sm:p-8 bg-c-primary/10 text-c-text shadow sm:rounded-lg">
                <div class="max-w-full">
                    <!-- Link to show all posts -->
            <a class="-my-3 rounded-lg border-2 border-c-accent/80 bg-c-accent/80 px-3 py-1 text-c-background hover:bg-c-accent active:border-c-primary"
            href="{{ route('posts.index') }}">All posts</a>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-c-primary/10 text-c-text shadow sm:rounded-lg">
                <div class="max-w-full">
                    @include('admin.partials.user-list')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

