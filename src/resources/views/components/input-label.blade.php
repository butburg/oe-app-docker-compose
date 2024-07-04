@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-yellow-text']) }}>
    {{ $value ?? $slot }}
</label>
