<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-outline-primary']) }}>
    {{ $slot }}
</button>
