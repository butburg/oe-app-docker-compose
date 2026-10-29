<x-guest-layout focused>
    <p class="text-xs font-semibold uppercase tracking-widest text-c-accent">{{ __('Almost done') }}</p>
    <h1 class="mt-1 text-2xl font-semibold text-c-text">{{ __('Hi :name, one last step!', ['name' => $user->name]) }}</h1>

    <p class="mt-2 text-sm leading-6 text-c-muted">
        {{ __('One click and your account uses') }} <strong class="text-c-text">{{ $user->pending_email }}</strong>.
    </p>

    <form class="mt-6" method="POST" action="{{ $action }}">
        @csrf
        <x-glow-button>{{ __('Confirm my new email') }}</x-glow-button>
    </form>
</x-guest-layout>
