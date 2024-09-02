<x-app-layout>
    <div class="overflow-hidden bg-white shadow-sm">
        <div class="p-6 text-gray-900">
            <div class="mb-4 text-sm text-gray-600">
                {{ __('Thanks for signing up! Before getting started, could you verify your email address :email by clicking on
                                the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.',['email' => Auth::user()->email]) }}
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-4 text-sm font-medium text-green-600">
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
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        type="submit">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
