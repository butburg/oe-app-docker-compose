<x-app-layout>
    <x-slot name="header">
        <x-header2>Profile</x-header2>
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <!-- New Section for Profile Image Update -->
            <div class="bg-c-primary/40 p-4 text-c-text shadow sm:rounded-lg sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-image-form')
                </div>
            </div>
            <div class="bg-c-primary/10 p-4 text-c-text shadow sm:rounded-lg sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="bg-c-primary/40 p-4 text-c-text shadow sm:rounded-lg sm:p-8">
                <div class="flex flex-col lg:flex-row lg:items-start lg:gap-1">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>

                    {{-- info box --}}
                    <div class="mt-4 max-w-xl flex-shrink-0 lg:ml-4">
                        <div class="flex rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-gray-800 dark:text-red-400"
                            role="alert">
                            <svg class="me-3 mt-[2px] inline h-4 w-4 flex-shrink-0" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                            </svg>
                            <span class="sr-only">Danger</span>
                            <div>
                                <span class="font-medium">Keep in mind if you want to change your username:</span>
                                <ul class="mt-1.5 list-inside list-disc">
                                    <li>A username is how others recognize you.</li>
                                    <li>You can only change your username once a month.</li>
                                    <li>All your previous posts and comments will display the new username.</li>
                                    <li>Sometimes, your old username may be shown as "formerly known as".</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-c-primary/10 p-4 text-c-text shadow sm:rounded-lg sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
