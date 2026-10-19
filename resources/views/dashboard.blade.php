<x-app-layout>
    <div class="page-col">
        <div class="card card-pad">
            <h1 class="h4">Welcome, {{ auth()->user()->name }}</h1>
            <p class="text-muted mb-3">Your home feed is coming soon.</p>
            <a class="btn btn-primary" href="{{ route('profile.show', auth()->user()) }}"><i class="bi bi-person-circle" aria-hidden="true"></i> View my profile</a>
        </div>
    </div>
</x-app-layout>
