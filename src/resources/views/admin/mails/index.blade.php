<x-app-layout>
    <x-slot name="header">
        <x-header2>Mail to users</x-header2>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-6 px-4 py-6 text-c-text sm:px-6 lg:px-8">
        <section class="app-surface p-4 sm:p-6">
            <header>
                <h3 class="text-lg font-semibold">Write a mail</h3>
                <p class="mt-1 text-sm leading-6 text-c-muted">
                    Announcements go to everyone in the group, also to users who switched off notification mails,
                    and cannot be unsubscribed. Keep them rare. Every user gets their own mail.
                </p>
            </header>

            <form method="POST" action="{{ route('admin.mails.preview') }}" class="mt-6 space-y-6"
                x-data="{ group: @js(old('group', 'all')) }">
                @csrf
                <div>
                    <x-input-label for="subject" value="Subject" />
                    <x-text-input id="subject" name="subject" type="text" class="mt-1 block w-full" maxlength="150"
                        :value="old('subject')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('subject')" />
                </div>

                <div>
                    <x-input-label for="body" value="Text" />
                    <textarea id="body" name="body" rows="10" maxlength="10000" required
                        class="app-field mt-1 font-mono text-sm">{{ old('body') }}</textarea>
                    <p class="mt-1 text-xs text-c-muted">Markdown: <code># Heading</code>, <code>**bold**</code>,
                        <code>[link](https://…)</code>, lists with <code>-</code>. HTML is shown as text.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('body')" />
                </div>

                <fieldset>
                    <legend class="block text-sm font-medium text-c-text">Recipients</legend>
                    <div class="mt-2 space-y-2">
                        @foreach ($groups as $value => $label)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="radio" name="group" value="{{ $value }}" x-model="group"
                                    class="border-c-line bg-c-surface-raised text-c-primary focus:ring-c-primary">
                                <span>{{ $label }}</span>
                                @if ($value !== 'user')
                                    <span class="text-c-muted">({{ $counts[$value] }} {{ Str::plural('user', $counts[$value]) }})</span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-3" x-show="group === 'user'" x-cloak>
                        <x-input-label for="recipient" value="User (name or email)" />
                        <x-text-input id="recipient" name="recipient" type="email" list="verified-users"
                            class="mt-1 block w-full" :value="old('recipient')" x-bind:required="group === 'user'" />
                        <datalist id="verified-users">
                            @foreach ($users as $user)
                                <option value="{{ $user->email }}">{{ $user->name }}</option>
                            @endforeach
                        </datalist>
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('group')" />
                    <x-input-error class="mt-2" :messages="$errors->get('recipient')" />
                </fieldset>

                <x-primary-button>Preview</x-primary-button>
            </form>
        </section>

        <section class="app-surface p-4 sm:p-6">
            <h3 class="text-lg font-semibold">Sent mails</h3>
            @if ($failedJobs > 0)
                <p class="mt-2 rounded-md border border-rose-400/50 bg-rose-500/10 p-3 text-sm text-rose-200">
                    {{ $failedJobs }} queued {{ Str::plural('job', $failedJobs) }} failed. See <code>php artisan queue:failed</code>.
                </p>
            @endif
            <ul class="mt-4 divide-y divide-c-line text-sm">
                @forelse ($sent as $mail)
                    <li class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 py-2">
                        <span class="min-w-0 break-words font-medium">{{ $mail->subject }}</span>
                        <span class="text-c-muted">
                            {{ $mail->group === 'user' ? $mail->recipient_email : $groups[$mail->group] ?? $mail->group }}
                            · {{ $mail->recipient_count }} {{ Str::plural('user', $mail->recipient_count) }}
                            · {{ $mail->user?->name ?? 'deleted admin' }}
                            · {{ $mail->created_at->format('d.m.Y H:i') }}
                        </span>
                    </li>
                @empty
                    <li class="py-2 text-c-muted">No mails sent yet.</li>
                @endforelse
            </ul>
        </section>
    </div>
</x-app-layout>
