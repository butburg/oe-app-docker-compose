<section>
    <header>
        <h2 class="text-lg font-medium text-content-text text-gray-400">
            {{ __('About Me') }}
        </h2>
    </header>   

    <p class="mt-2 text-sm text-gray-400">
        {{ $user->description ?? __('No description provided.') }}
    </p>
</section>
