<section class="mt-5">
    <header>
        <h2 class="text-base font-semibold text-c-muted">
            {{ __('About Me') }}
        </h2>
    </header>

    {{-- content inline, otherwise pre-line renders the template's line breaks --}}
    <p class="mt-1 whitespace-pre-line text-sm leading-6 text-c-muted">{{ $user->description ?? __('No description provided.') }}</p>
</section>
