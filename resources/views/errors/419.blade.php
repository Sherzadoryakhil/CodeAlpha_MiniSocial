<x-guest-layout title="Page expired">
    <div class="error-card">
        <div class="error-icon" aria-hidden="true"><i class="bi bi-hourglass-split"></i></div>
        <h1 class="h4 mb-2">419: This page has expired</h1>
        <p class="text-muted mb-0">For your safety the form was open for too long. Go back, reload the page and try again.</p>
        <div class="error-actions">
            <a class="btn btn-primary" href="{{ url('/') }}"><i class="bi bi-house-door-fill" aria-hidden="true"></i> Go to the home page</a>
            <button type="button" class="btn btn-outline-primary" onclick="history.back()"><i class="bi bi-arrow-left" aria-hidden="true"></i> Go back</button>
        </div>
    </div>
</x-guest-layout>
