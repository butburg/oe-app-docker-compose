<x-guest-layout focused>
    <p class="text-xs font-semibold uppercase tracking-widest text-c-accent">{{ __('Welcome') }}</p>
    <h1 class="mt-1 text-2xl font-semibold text-c-text">{{ __('Hi :name, great to have you!', ['name' => $pending->name]) }}</h1>

    <p class="mt-2 text-sm leading-6 text-c-muted">
        {{ __('One click and your account for') }} <strong class="text-c-text">{{ $pending->email }}</strong> {{ __('is ready to grow.') }}
    </p>

    <form class="mt-6" method="POST" action="{{ route('register.confirm', $token) }}">
        @csrf
        <x-glow-button>{{ __('Activate my account') }}</x-glow-button>
    </form>
</x-guest-layout>
