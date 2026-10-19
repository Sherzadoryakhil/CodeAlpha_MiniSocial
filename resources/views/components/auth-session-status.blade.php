@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'alert alert-success', 'role' => 'status']) }}>
        <i class="bi bi-check-circle-fill alert-icon" aria-hidden="true"></i>
        <div class="alert-body">{{ $status }}</div>
    </div>
@endif
