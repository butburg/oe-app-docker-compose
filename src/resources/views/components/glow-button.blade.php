{{-- gradient frame in the accent colors, fills on hover --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'group block w-full rounded-lg bg-gradient-to-r from-c-primary via-c-secondary to-c-accent p-0.5 shadow-lg shadow-c-primary/20 transition hover:shadow-c-secondary/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-c-accent focus-visible:ring-offset-2 focus-visible:ring-offset-c-background']) }}>
    <span class="flex min-h-11 items-center justify-center rounded-md bg-c-surface px-4 font-semibold text-c-text transition-colors group-hover:bg-transparent group-hover:text-c-background">
        {{ $slot }}
    </span>
</button>
