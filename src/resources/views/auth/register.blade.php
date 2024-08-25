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
<div class="relative mt-4">
    <x-input-label for="password" :value="__('New Password')" />

    <x-text-input class="mt-1 block w-full pr-10" id="password" name="password" type="password" required
        autocomplete="new-password" />

    <!-- Show/Hide Password Toggle -->
    <button class="absolute bottom-0 right-0 h-10 w-7 text-c-accent" type="button"
        onmousedown="showPassword()" onmouseup="hidePassword()" onmouseleave="hidePassword()"
        ontouchstart="showPassword()" ontouchend="hidePassword()">
        <svg class="bi bi-eye-fill" id="togglePasswordIcon" xmlns="http://www.w3.org/2000/svg"
            width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
            <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0" />
            <path
                d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7" />
        </svg>

    </button>

    <x-input-error class="mt-2" :messages="$errors->get('password')" />
</div>

            <script>
                function showPassword() {
                    const passwordField = document.getElementById('password');
                    passwordField.type = 'text';
                }

                function hidePassword() {
                    const passwordField = document.getElementById('password');
                    passwordField.type = 'password';
                }
            </script>

            {{-- Removed for ready of usage
            <!-- Confirm Password -->
            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

                <x-text-input class="mt-1 block w-full" id="password_confirmation" name="password_confirmation"
                    type="password" required autocomplete="new-password" />

                <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
            </div>
            --}}
            <!-- Check for privacy policy -->
            <div class="mt-4 flex items-center">
                <input
                    class="h-4 w-4 rounded border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500"
                    id="privacy_policy" name="privacy_policy" type="checkbox" value="1">
                <label class="ms-2 text-sm font-medium text-gray-900" for="privacy_policy">I agree
                    with the <a class="text-c-accent hover:underline" href="{{ route('impressum') }}">privacy
                        policy</a>.</label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('privacy_policy')" />
            <div class="mt-4 flex items-center justify-end">

                <x-primary-button class="ms-4">
                    {{ __('Register') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
