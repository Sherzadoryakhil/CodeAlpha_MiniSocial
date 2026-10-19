@props(['comment', 'actions' => true])

<div class="comment" id="comment-{{ $comment->id }}">
    <a href="{{ route('profile.show', $comment->user) }}" tabindex="-1" aria-hidden="true">
        <x-avatar :user="$comment->user" :size="36" />
    </a>

    <div class="comment-main">
        <div class="comment-bubble">
            <a class="comment-author" href="{{ route('profile.show', $comment->user) }}">{{ $comment->user->name }}</a>
            <span class="text-muted small">{{ '@' . $comment->user->username }}</span>
            <p class="mb-0 text-break text-pre-line" style="white-space: pre-line;">{{ $comment->body }}</p>
        </div>

        <div class="comment-meta">
            <time datetime="{{ $comment->created_at->toIso8601String() }}" title="{{ $comment->created_at->format('M j, Y g:i A') }}">
                {{ $comment->created_at->diffForHumans() }}
            </time>

            @if ($comment->updated_at->gt($comment->created_at))
                <span>(edited)</span>
            @endif

            @if ($actions)
                @can('update', $comment)
                    <a class="link-action" href="{{ route('comments.edit', $comment) }}"><i class="bi bi-pencil" aria-hidden="true"></i> Edit</a>

                    <form method="POST" action="{{ route('comments.destroy', $comment) }}"
                          onsubmit="return confirm('Delete this comment? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="link-action link-action--danger"><i class="bi bi-trash3" aria-hidden="true"></i> Delete</button>
                    </form>
                @endcan
            @endif
        </div>
    </div>
</div>
