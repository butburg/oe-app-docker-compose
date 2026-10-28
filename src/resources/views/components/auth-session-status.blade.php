@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'text-sm font-medium text-c-accent']) }} role="status" aria-live="polite">
        {{ $status }}
    </div>
@endif
