<x-app-layout>
    <x-slot name="header">
        <x-header2>Admin Dashboard</x-header2>

        {{-- Check if there is a notif.success flash session --}}
        @if (Session::has('notif.success'))
            <div class="-my-3 rounded-lg bg-blue-300 px-4 py-2">
                <span class="italic text-white">{{ Session::get('notif.success') }}</span>
            </div>
        @endif
        <span></span>
    </x-slot>

    
        <div class="flex flex-wrap justify-centersm:gap-4">
            @foreach ($users as $user)
                @include('components.profile.user-tile', ['user' => $user])
            @endforeach
        </div>
    
</x-app-layout>
