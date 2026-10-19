<x-guest-layout title="Page not found">
    <div class="error-card">
        <div class="error-icon" aria-hidden="true"><i class="bi bi-compass"></i></div>
        <h1 class="h4 mb-2">404: Page not found</h1>
        <p class="text-muted mb-0">We could not find what you were looking for. The address may be wrong, or the page may have been deleted.</p>
        <div class="error-actions">
            <a class="btn btn-primary" href="{{ url('/') }}"><i class="bi bi-house-door-fill" aria-hidden="true"></i> Go to the home page</a>
            <button type="button" class="btn btn-outline-primary" onclick="history.back()"><i class="bi bi-arrow-left" aria-hidden="true"></i> Go back</button>
        </div>
    </div>
</x-guest-layout>
