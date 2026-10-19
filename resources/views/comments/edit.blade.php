<x-app-layout title="Edit comment">
    <div class="page-col page-col--form">
        <p class="mb-2"><a class="back-link" href="{{ route('posts.show', $comment->post_id) }}#comment-{{ $comment->id }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to post</a></p>

        <div class="card card-pad">
            <h1 class="h4 mb-4">Edit comment</h1>

            <form method="POST" action="{{ route('comments.update', $comment) }}">
                @csrf
                @method('PATCH')

                <div class="mb-4">
                    <x-input-label for="body" value="Comment" />
                    <textarea id="body" name="body" rows="3" maxlength="500" required
                              class="form-control @error('body') is-invalid @enderror"
                              aria-describedby="bodyHelp">{{ old('body', $comment->body) }}</textarea>
                    <div id="bodyHelp" class="form-text">Up to 500 characters.</div>
                    <x-input-error :messages="$errors->get('body')" />
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <x-primary-button><i class="bi bi-check-lg" aria-hidden="true"></i> Save changes</x-primary-button>
                    <a class="btn btn-outline-primary" href="{{ route('posts.show', $comment->post_id) }}#comment-{{ $comment->id }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
