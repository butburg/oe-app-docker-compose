<x-app-layout>
    <div class="app-surface overflow-hidden">
        <div class="p-4 text-c-text sm:p-6">
            <div class="mb-4 text-sm leading-6 text-c-muted">
                {{ __('Please verify your email address :email by clicking on the link we emailed to you. Until then you see the gallery like a guest. If you didn\'t receive the email, we will gladly send you another.', ['email' => Auth::user()->email]) }}
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-4 text-sm font-medium text-c-accent" role="status" aria-live="polite">
                    {{ __('A new verification link has been sent to :email', ['email' => Auth::user()->email]) }}
                </div>
            @endif

            <div class="mt-4 flex items-center justify-between">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf

                    <div>
                        <x-primary-button>
                            {{ __('Resend Verification Email') }}
                        </x-primary-button>
                    </div>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button
                        class="rounded-sm text-sm text-c-muted underline underline-offset-4 hover:text-c-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-c-primary"
                        type="submit">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>

            <div class="mt-8 border-t border-c-line pt-6">
                @include('profile.partials.delete-user-form', ['action' => route('verification.account.destroy')])
            </div>
        </div>
    </div>
</x-app-layout>
