<button {{ $attributes->merge(['type' => 'submit', 'class' => 'app-button-danger']) }}>
    {{ $slot }}
</button>
