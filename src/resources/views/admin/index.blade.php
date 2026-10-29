<x-app-layout>
    <x-slot name="header">
        <x-header2>Users</x-header2>
    </x-slot>


    <div class="mx-auto flex max-w-7xl flex-wrap justify-center gap-4 px-4 py-6 sm:px-6 lg:px-8">
        @foreach ($users as $user)
            @include('components.profile.user-tile', ['user' => $user])
        @endforeach
    </div>

</x-app-layout>
