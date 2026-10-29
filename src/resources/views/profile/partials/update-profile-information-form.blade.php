<section>
    <header>
        <h2 class="text-lg font-semibold text-c-text">
            {{ __('Profile Information') }}
        </h2>
        <p class="mt-1 text-sm leading-6 text-c-muted">
            {{ __("Update your account's profile information and email address. A username is how others recognize you.") }}
        </p>
    </header>

    <!-- Form for updating username -->
    <form method="post" action="{{ route('profile.updateName') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input class="mt-1 block w-full" id="name" name="name" type="text" :value="old('name', $user->name)"
                required autocomplete="name" maxlength="120" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>
        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Update Username') }}</x-primary-button>
            @if (session('status') === 'profile-updated')
                <p class="text-sm text-c-accent" x-data="{ show: true }" x-show="show" x-transition
                    x-init="setTimeout(() => show = false, 4000)">
                    {{ __('Username updated.') }}
                </p>
            @endif
        </div>
    </form>

    <!-- Form for updating email -->
    <form method="post" action="{{ route('profile.updateEmail') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input class="mt-1 block w-full" id="email" name="email" type="email" :value="old('email', $user->email)"
                required autocomplete="email" maxlength="120" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>
        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Update Email') }}</x-primary-button>
        </div>
    </form>

    {{-- A new email waits for its link, the current one stays active until then --}}
    @if ($user->pending_email)
        <div class="mt-4 rounded-md border border-c-line bg-c-surface-raised p-3 text-sm text-c-muted">
            <p>
                {{ __('Waiting for confirmation of') }} <strong class="text-c-text">{{ $user->pending_email }}</strong>.
                {{ __('Until you open the link in that mail, you keep using :email.', ['email' => $user->email]) }}
            </p>
            <div class="mt-3 flex flex-wrap items-center gap-4">
                <form method="POST" action="{{ route('profile.email.resend') }}">
                    @csrf
                    <button type="submit"
                        class="rounded-sm text-sm text-c-primary underline underline-offset-4 hover:text-c-text focus:outline-none focus-visible:ring-2 focus-visible:ring-c-primary">
                        {{ __('Resend link') }}
                    </button>
                </form>
                <form method="POST" action="{{ route('profile.email.cancel') }}">
                    @csrf
                    @method('delete')
                    <button type="submit"
                        class="rounded-sm text-sm text-c-muted underline underline-offset-4 hover:text-c-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-c-primary">
                        {{ __('Cancel email change') }}
                    </button>
                </form>
            </div>
        </div>
    @endif
</section>
