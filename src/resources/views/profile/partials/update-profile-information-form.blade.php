<section>
    <header>
        <h2 class="text-content-text text-lg font-medium">
            {{ __('Profile Information') }}
        </h2>
        <p class="mt-1 text-sm text-gray-300">
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
                <p class="text-sm text-gray-100" x-data="{ show: true }" x-show="show" x-transition
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
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-gray-800">
                        {{ __('Your email address is unverified.') }}
                        <button
                            class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            form="send-verification">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>
        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Update Email') }}</x-primary-button>
        </div>
    </form>
</section>
