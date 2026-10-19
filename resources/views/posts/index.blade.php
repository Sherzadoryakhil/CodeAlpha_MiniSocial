<x-app-layout title="Home">
    <div class="page-col">

        <section class="card card-pad mb-4" aria-labelledby="new-post-heading">
            <div class="composer">
                <x-avatar :user="auth()->user()" :size="44" class="d-none d-sm-block" />

                <div class="composer-body">
                    <h1 class="h5 composer-heading" id="new-post-heading">Create a post</h1>

                    <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <x-input-label for="body" value="What's on your mind?" />
                            <textarea id="body" name="body" rows="3" maxlength="1000" required
                                      class="form-control @error('body') is-invalid @enderror"
                                      aria-describedby="bodyHelp"
                                      placeholder="Share an update...">{{ old('body') }}</textarea>
                            <div id="bodyHelp" class="form-text">Up to 1000 characters.</div>
                            <x-input-error :messages="$errors->get('body')" />
                        </div>

                        <div class="mb-3">
                            <x-input-label for="image" value="Add an image (optional)" />
                            <input id="image" name="image" type="file"
                                   class="form-control @error('image') is-invalid @enderror"
                                   accept="image/png,image/jpeg,image/webp" aria-describedby="imageHelp">
                            <div id="imageHelp" class="form-text">JPG, PNG or WebP, up to 2 MB.</div>
                            <x-input-error :messages="$errors->get('image')" />
                        </div>

                        <x-primary-button><i class="bi bi-send-fill" aria-hidden="true"></i> Post</x-primary-button>
                    </form>
                </div>
            </div>
        </section>

        <h2 class="section-title">Latest posts</h2>

        @forelse ($posts as $post)
            <x-post-card :post="$post" />
        @empty
            <div class="card empty-state">
                <div class="empty-icon" aria-hidden="true"><i class="bi bi-journal-richtext"></i></div>
                <p>No posts yet. Be the first to post!</p>
            </div>
        @endforelse

        {{ $posts->links('pagination::bootstrap-5') }}
    </div>
</x-app-layout>
