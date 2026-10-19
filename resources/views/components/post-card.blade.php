@props(['post', 'showComments' => true])

@php
    $likes = $post->likes_count ?? 0;
    $comments = $post->comments_count ?? 0;
    $liked = (bool) ($post->liked_by_me ?? false);
    $preview = $post->relationLoaded('latestComments') ? $post->getRelation('latestComments') : collect();
@endphp

<article class="card post-card" id="post-{{ $post->id }}">
    <div class="card-body">
        <div class="post-head">
            <a href="{{ route('profile.show', $post->user) }}" tabindex="-1" aria-hidden="true">
                <x-avatar :user="$post->user" :size="48" :alt="$post->user->name" />
            </a>

            <div class="flex-grow-1 text-break min-w-0">
                <a class="post-author" href="{{ route('profile.show', $post->user) }}">{{ $post->user->name }}</a>
                <div class="post-meta">
                    {{ '@' . $post->user->username }} &middot;
                    <a href="{{ route('posts.show', $post) }}">
                        <time datetime="{{ $post->created_at->toIso8601String() }}" title="{{ $post->created_at->format('M j, Y g:i A') }}">
                            {{ $post->created_at->diffForHumans() }}
                        </time>
                    </a>
                </div>
            </div>
        </div>

        <p class="post-body text-break" style="white-space: pre-line;">{{ $post->body }}</p>

        @if ($post->image_path)
            <img src="{{ $post->image_url }}" class="post-image" loading="lazy"
                 alt="Image attached to the post by {{ $post->user->name }}">
        @endif

        <div class="post-stats">
            <span><i class="bi bi-heart-fill" aria-hidden="true"></i>{{ $likes }} {{ \Illuminate\Support\Str::plural('like', $likes) }}</span>
            <a href="{{ route('posts.show', $post) }}#comments"><i class="bi bi-chat-fill" aria-hidden="true"></i>{{ $comments }} {{ \Illuminate\Support\Str::plural('comment', $comments) }}</a>
        </div>

        <div class="post-actions">
            @if ($liked)
                <form method="POST" action="{{ route('posts.unlike', $post) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-liked action-btn" aria-pressed="true">
                        <i class="bi bi-heart-fill" aria-hidden="true"></i> Like
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('posts.like', $post) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary action-btn" aria-pressed="false">
                        <i class="bi bi-heart" aria-hidden="true"></i> Like
                    </button>
                </form>
            @endif

            <a class="btn btn-sm btn-outline-primary action-btn" href="{{ route('posts.show', $post) }}#comment-form"><i class="bi bi-chat" aria-hidden="true"></i> Comment</a>

            @can('update', $post)
                <div class="owner-actions">
                    <a class="btn btn-sm btn-outline-primary action-btn" href="{{ route('posts.edit', $post) }}"><i class="bi bi-pencil" aria-hidden="true"></i> Edit</a>

                    <form method="POST" action="{{ route('posts.destroy', $post) }}"
                          onsubmit="return confirm('Delete this post? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger action-btn"><i class="bi bi-trash3" aria-hidden="true"></i> Delete</button>
                    </form>
                </div>
            @endcan
        </div>

        @if ($showComments && $preview->isNotEmpty())
            <div class="post-comments">
                @if ($comments > $preview->count())
                    <p class="mb-1">
                        <a class="view-all" href="{{ route('posts.show', $post) }}#comments">View all {{ $comments }} comments</a>
                    </p>
                @endif

                @foreach ($preview as $comment)
                    <x-comment :comment="$comment" :actions="false" />
                @endforeach
            </div>
        @endif
    </div>
</article>
