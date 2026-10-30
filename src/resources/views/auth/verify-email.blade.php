{{-- Old accounts from before FEAT-013 that are still unverified: the same page as a pending registration,
     but the account stays, the way out is logging out --}}
<x-guest-layout>
    <x-check-email :email="Auth::user()->email" :resend-action="route('verification.send')" :email-action="route('verification.email')"
        :hint="__('Open it within :minutes minutes to confirm your email. Until then you see the gallery like a guest.', ['minutes' => config('auth.verification.expire', 60)])">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="rounded-sm text-sm text-c-muted underline underline-offset-4 hover:text-c-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-c-primary">
                {{ __('Log Out') }}
            </button>
        </form>
    </x-check-email>
</x-guest-layout>
