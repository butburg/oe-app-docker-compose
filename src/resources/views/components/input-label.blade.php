@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-red-800']) }}>
    {{ $value ?? $slot }}
</label>
