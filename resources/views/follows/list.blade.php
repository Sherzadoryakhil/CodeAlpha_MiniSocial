<x-app-layout :title="$title . ' of ' . $user->name">
    <div class="page-col">
        <p class="mb-2"><a class="back-link" href="{{ route('profile.show', $user) }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to {{ $user->name }}'s profile</a></p>

        <h1 class="h4 mb-1">{{ $title }}</h1>
        <p class="text-muted">{{ '@' . $user->username }}</p>

        <nav class="pill-tabs" aria-label="Follow lists">
            <a href="{{ route('follows.followers', $user) }}"
               @if (request()->routeIs('follows.followers')) aria-current="page" @endif>Followers</a>
            <a href="{{ route('follows.following', $user) }}"
               @if (request()->routeIs('follows.following')) aria-current="page" @endif>Following</a>
        </nav>

        @if ($users->isEmpty())
            <div class="card empty-state">
                <div class="empty-icon" aria-hidden="true"><i class="bi bi-people"></i></div>
                <p>{{ $emptyText }}</p>
            </div>
        @else
            <ul class="card person-list mb-3">
                @foreach ($users as $person)
                    <li class="person-item">
                        <a href="{{ route('profile.show', $person) }}" tabindex="-1" aria-hidden="true">
                            <x-avatar :user="$person" :size="52" :alt="$person->name" />
                        </a>

                        <div class="flex-grow-1 text-break min-w-0">
                            <a class="person-name" href="{{ route('profile.show', $person) }}">{{ $person->name }}</a>
                            <div class="text-muted small">{{ '@' . $person->username }}</div>
                            @if ($person->bio)
                                <div class="small text-break">{{ \Illuminate\Support\Str::limit($person->bio, 80) }}</div>
                            @endif
                        </div>

                        <div class="follow-slot">
                            <x-follow-button :user="$person" :following="in_array($person->id, $followingIds)" :small="true" />
                        </div>
                    </li>
                @endforeach
            </ul>

            {{ $users->links('pagination::bootstrap-5') }}
        @endif
    </div>
</x-app-layout>
