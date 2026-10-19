<x-app-layout :title="'Post by ' . $post->user->name">
    <div class="page-col">
        <p class="mb-2"><a class="back-link" href="{{ route('dashboard') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to feed</a></p>

        <h1 class="visually-hidden">Post by {{ $post->user->name }}</h1>

        <x-post-card :post="$post" :show-comments="false" />

        <section aria-labelledby="comments">
            <h2 class="section-title" id="comments">
                Comments ({{ $comments->count() }})
            </h2>

            <form id="comment-form" method="POST" action="{{ route('posts.comments.store', $post) }}" class="card card-pad comment-form-card">
                @csrf

                <x-input-label for="comment-body" value="Add a comment" />
                <textarea id="comment-body" name="body" rows="2" maxlength="500" required
                          class="form-control mb-2 @error('body') is-invalid @enderror"
                          aria-describedby="commentHelp"
                          placeholder="Write a comment...">{{ old('body') }}</textarea>
                <div id="commentHelp" class="form-text mb-2">Up to 500 characters.</div>
                <x-input-error :messages="$errors->get('body')" />

                <div class="mt-3">
                    <x-primary-button><i class="bi bi-chat-dots-fill" aria-hidden="true"></i> Add comment</x-primary-button>
                </div>
            </form>

            @forelse ($comments as $comment)
                <x-comment :comment="$comment" />
            @empty
                <div class="card empty-state">
                    <div class="empty-icon" aria-hidden="true"><i class="bi bi-chat-square-heart"></i></div>
                    <p>No comments yet. Be the first to comment!</p>
                </div>
            @endforelse
        </section>
    </div>
</x-app-layout>
