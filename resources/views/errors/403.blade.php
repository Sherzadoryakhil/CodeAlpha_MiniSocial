<x-guest-layout title="Not allowed">
    <div class="error-card">
        <div class="error-icon" aria-hidden="true"><i class="bi bi-shield-lock"></i></div>
        <h1 class="h4 mb-2">403: You are not allowed to do that</h1>
        <p class="text-muted mb-0">This page or action belongs to someone else. If you think this is a mistake, go back and try again.</p>
        <div class="error-actions">
            <a class="btn btn-primary" href="{{ url('/') }}"><i class="bi bi-house-door-fill" aria-hidden="true"></i> Go to the home page</a>
            <button type="button" class="btn btn-outline-primary" onclick="history.back()"><i class="bi bi-arrow-left" aria-hidden="true"></i> Go back</button>
        </div>
    </div>
</x-guest-layout>
