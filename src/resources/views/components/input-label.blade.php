@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-c-muted']) }}>
    {{ $value ?? $slot }}
</label>
