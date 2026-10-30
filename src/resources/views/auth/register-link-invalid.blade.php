<x-guest-layout>
    <h1 class="text-lg font-semibold text-c-text">{{ __('This link does not work (any more)') }}</h1>

    <p class="mt-2 text-sm leading-6 text-c-muted">
        {{ $reason ?? __('It has expired, was already used, or a newer link was sent. Please register again, or log in if your account already exists.') }}
    </p>

    <div class="mt-6 flex items-center justify-between">
        <a class="rounded-sm text-sm text-c-muted underline underline-offset-4 hover:text-c-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-c-primary"
            href="{{ route('login') }}">{{ __('Log in') }}</a>
        <a class="app-button-primary" href="{{ route('register') }}">{{ __('Register') }}</a>
    </div>
</x-guest-layout>
