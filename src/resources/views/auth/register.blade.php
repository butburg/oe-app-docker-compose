<x-guest-layout>
    <div class="pt-3">
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Username (visible to others)')" />
                <x-text-input class="mt-1 block w-full" id="name" name="name" type="text" :value="old('name')"
                    placeholder="Choose a unique username" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <!-- Email Address -->
            <div class="mt-4">
                <x-input-label for="email" :value="'Email (only for login)'" />
                <x-text-input class="mt-1 block w-full" id="email" name="email" type="email" :value="old('email')"
                    required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />

                <x-text-input class="mt-1 block w-full" id="password" name="password" type="password" required
                    autocomplete="new-password" />

                <x-input-error class="mt-2" :messages="$errors->get('password')" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

                <x-text-input class="mt-1 block w-full" id="password_confirmation" name="password_confirmation"
                    type="password" required autocomplete="new-password" />

                <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
            </div>

            <div class="mt-4 flex items-center">
                <input
                    class="h-4 w-4 rounded border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500"
                    id="link-checkbox" type="checkbox" value="">
                <label class="ms-2 text-sm font-medium text-gray-900" for="link-checkbox">I agree
                    with the <a class="text-c-accent hover:underline" href="{{route('impressum')}}">privacy policy</a>.</label>
            </div>

            <div class="mt-4 flex items-center justify-end">

                <x-primary-button class="ms-4">
                    {{ __('Register') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
