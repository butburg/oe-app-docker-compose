<section>
    <header>
        <h2 class="text-lg font-semibold text-c-text">
            {{ __('Mail notifications') }}
        </h2>
        <p class="mt-1 text-sm leading-6 text-c-muted">
            {{ __('Choose which mails you want to get. Important announcements from the admins always arrive.') }}
            {{ __('Everything is collected and sent together in one mail, at most one mail per :minutes minutes.', ['minutes' => \App\Jobs\SendMailBundle::minutes()]) }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.notifications.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="space-y-4">
            @foreach (\App\Models\User::NOTIFICATION_SETTINGS as $setting => $info)
                <div>
                    {{-- unchecked boxes are not sent, the hidden 0 switches the setting off --}}
                    <input type="hidden" name="{{ $setting }}" value="0">
                    @include('components.checkbox', [
                        'id' => $setting,
                        'name' => $setting,
                        'checked' => $user->{$setting},
                        'label' => e(__($info['label'])),
                    ])
                    <p class="ms-6 mt-1 text-sm text-c-muted">{{ __($info['description']) }}</p>
                </div>
            @endforeach
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
            @if (session('status') === 'notifications-updated')
                <p class="text-sm text-c-accent" x-data="{ show: true }" x-show="show" x-transition
                    x-init="setTimeout(() => show = false, 4000)">
                    {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>
