@props(['messages'])

@if ($messages)
    <div {{ $attributes->merge(['class' => 'field-error']) }} role="alert">
        <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
        <div>
            @foreach ((array) $messages as $message)
                <div><span class="fw-semibold">Error:</span> {{ $message }}</div>
            @endforeach
        </div>
    </div>
@endif
