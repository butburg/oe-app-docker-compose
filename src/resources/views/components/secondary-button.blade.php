<button {{ $attributes->merge(['type' => 'button', 'class' => 'app-button-secondary']) }}>
    {{ $slot }}
</button>
