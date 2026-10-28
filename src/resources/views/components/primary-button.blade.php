<button {{ $attributes->merge(['type' => 'submit', 'class' => 'app-button-primary']) }}>
    {{ $slot }}
</button>
