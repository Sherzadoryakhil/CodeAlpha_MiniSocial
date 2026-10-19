<x-guest-layout title="Too many requests">
    <div class="error-card">
        <div class="error-icon" aria-hidden="true"><i class="bi bi-speedometer2"></i></div>
        <h1 class="h4 mb-2">429: Too many requests</h1>
        <p class="text-muted mb-0">You are doing that too quickly. Please wait a minute and try again.</p>
        <div class="error-actions">
            <a class="btn btn-primary" href="{{ url('/') }}"><i class="bi bi-house-door-fill" aria-hidden="true"></i> Go to the home page</a>
            <button type="button" class="btn btn-outline-primary" onclick="history.back()"><i class="bi bi-arrow-left" aria-hidden="true"></i> Go back</button>
        </div>
    </div>
</x-guest-layout>
