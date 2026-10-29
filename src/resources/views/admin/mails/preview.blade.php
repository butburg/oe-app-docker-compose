<x-app-layout>
    <x-slot name="header">
        <x-header2>Announcement preview</x-header2>
    </x-slot>

    @php
        $fields = ['subject' => $data['subject'], 'body' => $data['body'], 'group' => $data['group'], 'recipient' => $data['recipient'] ?? ''];
    @endphp

    <div class="mx-auto max-w-3xl space-y-6 px-4 py-6 text-c-text sm:px-6 lg:px-8">
        <section class="app-surface p-4 sm:p-6">
            <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 text-sm">
                <dt class="text-c-muted">Subject</dt>
                <dd class="break-words font-medium">{{ $data['subject'] }}</dd>
                <dt class="text-c-muted">To</dt>
                <dd>{{ $data['group'] === 'user' ? $data['recipient'] : $groupLabel }}
                    ({{ $count }} {{ Str::plural('user', $count) }})</dd>
            </dl>

            <p class="mt-4 text-xs text-c-muted">Placeholders are filled with your own data here and in the test mail.</p>
            <iframe title="Mail preview" srcdoc="{{ $html }}" sandbox
                class="mt-2 h-[32rem] w-full rounded-md border border-c-line bg-c-background"></iframe>

            <div class="mt-4 flex flex-wrap items-center gap-3">
                {{-- Back to the form or a test to the own address: same fields again --}}
                <form method="POST" action="{{ route('admin.mails.preview') }}" class="flex flex-wrap gap-3">
                    @csrf
                    @foreach ($fields as $name => $value)
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endforeach
                    <button type="submit" name="action" value="edit" class="app-button-secondary">Edit</button>
                    <button type="submit" name="action" value="test" class="app-button-secondary">Send test to me</button>
                </form>

                <form method="POST" action="{{ route('admin.mails.send') }}" x-data="{ sending: false }"
                    @submit="if (!confirm(@js("Send this mail to {$count} " . Str::plural('user', $count) . '?'))) { $event.preventDefault(); return; } sending = true">
                    @csrf
                    @foreach ($fields as $name => $value)
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endforeach
                    <input type="hidden" name="send_token" value="{{ $sendToken }}">
                    <x-primary-button x-bind:disabled="sending" :disabled="$count === 0">
                        Send to {{ $count }} {{ Str::plural('user', $count) }}
                    </x-primary-button>
                </form>
            </div>
            {{-- FEAT-020: announcements wait for the mail bundle like all other mails, the test mail does not --}}
            <p class="mt-3 text-sm text-c-muted">
                Each user gets it with their next mail bundle, at the latest in {{ \App\Jobs\SendMailBundle::minutes() }} minutes.
            </p>
        </section>
    </div>
</x-app-layout>
