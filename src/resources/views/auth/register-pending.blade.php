<x-guest-layout focused>
    <h1 class="text-lg font-semibold text-c-text">{{ __('Check your email') }}</h1>

    <p class="mt-2 text-sm leading-6 text-c-muted">
        {{ __('We sent a link to') }} <strong class="text-c-text">{{ $email }}</strong>.
        {{ __('Open it within :minutes minutes to create your account.', ['minutes' => \App\Models\PendingRegistration::VALID_MINUTES]) }}
    </p>

    @if (session('status') == 'link-sent')
        <div class="mt-4 block rounded-md border border-c-accent/30 bg-c-accent/10 px-3 py-2 text-sm text-c-text"
            role="status" aria-live="polite">
            {{ __('A new link has been sent to :email. Only the newest link works.', ['email' => $email]) }}
        </div>
    @endif

    {{-- Change email, open right away when the new address was invalid --}}
    <div class="mt-6" x-data="{ edit: {{ $errors->has('email') ? 'true' : 'false' }} }">
        <form method="POST" action="{{ route('register.pending.email') }}" x-show="edit" x-cloak>
            @csrf
            @method('patch')

            <x-input-label for="email" :value="__('Correct email')" />
            <x-text-input class="mt-1 block w-full" id="email" name="email" type="email"
                :value="old('email', $email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            <div class="mt-3 flex items-center justify-end gap-3">
                <x-secondary-button type="button" @click="edit = false">{{ __('Cancel') }}</x-secondary-button>
                <x-primary-button>{{ __('Send to new email') }}</x-primary-button>
            </div>
        </form>

        <div class="flex flex-wrap items-center justify-between gap-3" x-show="!edit">
            <form method="POST" action="{{ route('register.pending.resend') }}">
                @csrf
                <x-primary-button>{{ __('Resend') }}</x-primary-button>
            </form>

            <button type="button" @click="edit = true"
                class="rounded-sm text-sm text-c-muted underline underline-offset-4 hover:text-c-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-c-primary">
                {{ __('Change email') }}
            </button>

            <form method="POST" action="{{ route('register.pending.destroy') }}">
                @csrf
                @method('delete')
                <button type="submit"
                    class="rounded-sm text-sm text-c-muted underline underline-offset-4 hover:text-c-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-c-primary">
                    {{ __('Abort') }}
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
