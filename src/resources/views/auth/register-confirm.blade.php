<x-guest-layout focused>
    <p class="text-xs font-semibold uppercase tracking-widest text-c-accent">{{ __('Welcome') }}</p>
    <h1 class="mt-1 text-2xl font-semibold text-c-text">{{ __('Hi :name, great to have you!', ['name' => $pending->name]) }}</h1>

    <p class="mt-2 text-sm leading-6 text-c-muted">
        {{ __('One click and your account for') }} <strong class="text-c-text">{{ $pending->email }}</strong> {{ __('is ready to grow.') }}
    </p>

    <form class="mt-6" method="POST" action="{{ route('register.confirm', $token) }}">
        @csrf
        {{-- gradient frame in the accent colors, fills on hover --}}
        <button type="submit"
            class="group block w-full rounded-lg bg-gradient-to-r from-c-primary via-c-secondary to-c-accent p-0.5 shadow-lg shadow-c-primary/20 transition hover:shadow-c-secondary/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-c-accent focus-visible:ring-offset-2 focus-visible:ring-offset-c-background">
            <span class="flex min-h-11 items-center justify-center rounded-md bg-c-surface px-4 font-semibold text-c-text transition-colors group-hover:bg-transparent group-hover:text-c-background">
                {{ __('Activate my account') }}
            </span>
        </button>
    </form>
</x-guest-layout>
