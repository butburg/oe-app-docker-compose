<x-guest-layout>
    <div class="mb-4 text-sm leading-6 text-c-muted">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div
            class="mb-4 block rounded-md border border-c-accent/30 bg-c-accent/10 px-3 py-2 text-sm text-c-text" role="status" aria-live="polite">
            {{ session('status') }}</div>
    @endif
    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input class="mt-1 block w-full" id="email" name="email" type="email" :value="old('email')" required
                autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div class="mt-4 flex items-center justify-end">
            <x-primary-button>
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
