<x-guest-layout focused>
    <x-check-email :email="$email" :resend-action="route('register.pending.resend')" :email-action="route('register.pending.email')"
        :hint="__('Open it within :minutes minutes to create your account.', ['minutes' => \App\Models\PendingRegistration::VALID_MINUTES])">
        <form method="POST" action="{{ route('register.pending.destroy') }}">
            @csrf
            @method('delete')
            <button type="submit"
                class="rounded-sm text-sm text-c-muted underline underline-offset-4 hover:text-c-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-c-primary">
                {{ __('Abort') }}
            </button>
        </form>
    </x-check-email>
</x-guest-layout>
