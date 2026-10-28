<x-app-layout>
    <x-slot name="header">
        <x-header2>Admin Dashboard</x-header2>
    </x-slot>


        <div class="flex flex-wrap justify-centersm:gap-4">
            @foreach ($users as $user)
                @include('components.profile.user-tile', ['user' => $user])
            @endforeach
        </div>

</x-app-layout>
