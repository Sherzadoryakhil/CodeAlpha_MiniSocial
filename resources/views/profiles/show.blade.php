<x-app-layout :title="$user->name">
    <div class="page-col">

        <section class="card profile-card" aria-label="Profile">
            <div class="profile-cover" aria-hidden="true"></div>

            <div class="profile-body">
                <div class="profile-top">
                    <x-avatar :user="$user" :size="104" :alt="'Profile photo of ' . $user->name" class="profile-avatar" />

                    <div class="profile-cta">
                        @if (auth()->id() === $user->id)
                            <a id="edit-profile-button" class="btn btn-outline-primary" href="{{ route('profile.edit') }}"><i class="bi bi-pencil-square" aria-hidden="true"></i> Edit profile</a>
                        @else
                            <x-follow-button :user="$user" :following="$isFollowing" />
                        @endif
                    </div>
                </div>

                <h1 class="profile-name h4">{{ $user->name }}</h1>
                <p class="profile-username">{{ '@' . $user->username }}</p>

                @if ($user->bio)
                    <p class="profile-bio text-break">{{ $user->bio }}</p>
                @endif

                <ul class="profile-stats">
                    <li><span class="stat"><strong>{{ $user->posts_count }}</strong> Posts</span></li>
                    <li>
                        <a href="{{ route('follows.followers', $user) }}"><strong>{{ $user->followers_count }}</strong> Followers</a>
                    </li>
                    <li>
                        <a href="{{ route('follows.following', $user) }}"><strong>{{ $user->following_count }}</strong> Following</a>
                    </li>
                </ul>
            </div>
        </section>

        <h2 class="section-title">Posts</h2>

        @forelse ($posts as $post)
            <x-post-card :post="$post" />
        @empty
            <div class="card empty-state">
                <div class="empty-icon" aria-hidden="true"><i class="bi bi-card-text"></i></div>
                <p>No posts yet.</p>
            </div>
        @endforelse

        {{ $posts->links('pagination::bootstrap-5') }}
    </div>
</x-app-layout>
