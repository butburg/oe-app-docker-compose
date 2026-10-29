@props(['messages'])

@if ($messages)
    {{-- data-form-error: app.js scrolls to the first one after a reload --}}
    <ul {{ $attributes->merge(['class' => 'space-y-1 text-sm font-medium text-rose-300']) }} data-form-error role="alert">
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
