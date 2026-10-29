{{-- Small tiles showing the users. Used for the Admin dashboard. --}}
<div
    class="app-surface flex w-full flex-col p-4 sm:max-w-xs sm:flex-shrink sm:flex-grow sm:basis-[320px] sm:p-5">

    <div class="flex items-center gap-3">
        <!-- User Image -->
        @php
            $isOnline = $user->session ? 'border-2 border-c-accent' : 'border-2 border-c-line';
        @endphp
        <a class="transform transition-transform hover:scale-105"
            href="{{ $user ? route('profile.show', $user) : '' }}">
            @include('components.image_or_placeholder', [
                'image' => $user->image,
                'size_type' => 'xs',
                'alt_title' => 'Your Profile Image',
                'style' => "{$isOnline} my-2 h-20 w-20 rounded-full bg-c-surface-raised object-cover",
                'placeholder' => 'images/starfish.svg',
            ])
        </a>

        <!-- User Stats -->
        @include('components.profile.user-stats', [
            'user' => $user,
        ])
    </div>

    <!-- Former Name and Title -->
    @include('components.profile.user-name', ['user' => $user])
    <p class="mb-2 truncate text-sm text-c-muted">{{ $user->email }}
    </p>

    <!-- User Type -->
    <p class="mb-2 text-sm text-c-text">
        <strong>{{ $user->usertype }}</strong>
    </p>

    <!-- Timestamp Group -->
    <div class="mt-2 text-sm text-c-muted">
        <p>
            <strong>Seen:</strong>
            @if ($user->session)
                {{ \Carbon\Carbon::parse($user->session->last_activity)->diffForHumans() }}
            @else
                Offline
            @endif
        </p>
        <p class="mb-1">
            <strong>Last Updated:</strong>
            {{ $user->updated_at->format('d.m.y H:i') }}
        </p>
        <p class="mb-1">
            <strong>Registered:</strong>
            {{ $user->created_at->format('d.m.y H:i') }}
        </p>
        <p>
            @if ($user->email_verified_at)
                <strong>Email Verified:</strong>
                {{ $user->email_verified_at->format('d.m.y H:i') }}
            @else
                <strong>Email Verified:</strong> <span
                    class="text-rose-300">No</span>
            @endif
        </p>
    </div>

    {{-- Admins can only delete regular users, never themselves or other admins --}}
    @if ($user->usertype !== 'admin' && !$user->is(auth()->user()))
        @php
            $modalName = "confirm-admin-user-deletion-{$user->id}";
            $hasErrors = $errors->adminUserDeletion->isNotEmpty() && old('target_user') == $user->id;
        @endphp

        <div class="mt-4 flex justify-end">
            <x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', '{{ $modalName }}')">
                {{ __('Delete User') }}
            </x-danger-button>
        </div>

        <x-modal name="{{ $modalName }}" :show="$hasErrors" focusable>
            <form class="p-6" method="post" action="{{ route('admin.users.destroy', $user) }}">
                @csrf
                @method('delete')
                <input name="target_user" type="hidden" value="{{ $user->id }}" />

                <h2 class="text-lg font-semibold text-c-text">
                    {{ __('Delete :name?', ['name' => $user->name]) }}
                </h2>

                <p class="mt-1 text-sm leading-6 text-c-muted">
                    {{ __('This permanently deletes the account and its profile image. Type the email address of this user to confirm.') }}
                </p>

                <div class="mt-6">
                    <x-input-label class="sr-only" for="confirm_email_{{ $user->id }}" value="{{ __('Email') }}" />

                    <x-text-input class="mt-1 block w-full" id="confirm_email_{{ $user->id }}" name="confirm_email"
                        type="text" autocomplete="off" placeholder="{{ $user->email }}" />

                    @if ($hasErrors)
                        <x-input-error class="mt-2" :messages="$errors->adminUserDeletion->get('confirm_email')" />
                    @endif
                </div>

                <div class="mt-6">
                    <div class="mt-4">
                        @include('components.checkbox', [
                            'id' => "delete_comments_{$user->id}",
                            'name' => 'delete_comments',
                            'label' => 'Delete all Comments of this user',
                        ])
                    </div>

                    <div class="mt-4">
                        @include('components.checkbox', [
                            'id' => "delete_posts_{$user->id}",
                            'name' => 'delete_posts',
                            'label' => 'Delete all Posts of this user',
                        ])
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        {{ __('Cancel') }}
                    </x-secondary-button>

                    <x-danger-button class="ms-3">
                        {{ __('Delete User') }}
                    </x-danger-button>
                </div>
            </form>
        </x-modal>
    @endif

</div>
