<x-app-layout title="Edit post">
    <div class="page-col page-col--form">
        <p class="mb-2"><a class="back-link" href="{{ route('posts.show', $post) }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to post</a></p>

        <div class="card card-pad">
            <h1 class="h4 mb-4">Edit post</h1>

            <form method="POST" action="{{ route('posts.update', $post) }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="mb-3">
                    <x-input-label for="body" value="Post text" />
                    <textarea id="body" name="body" rows="4" maxlength="1000" required
                              class="form-control @error('body') is-invalid @enderror"
                              aria-describedby="bodyHelp">{{ old('body', $post->body) }}</textarea>
                    <div id="bodyHelp" class="form-text">Up to 1000 characters.</div>
                    <x-input-error :messages="$errors->get('body')" />
                </div>

                @if ($post->image_path)
                    <div class="mb-3">
                        <p class="form-label mb-2">Current image</p>
                        <img src="{{ $post->image_url }}" class="post-image mb-2" style="max-height: 260px;"
                             alt="Image currently attached to this post">
                        <div class="form-check">
                            <input id="remove_image" name="remove_image" type="checkbox" value="1" class="form-check-input">
                            <label for="remove_image" class="form-check-label">Remove this image</label>
                        </div>
                    </div>
                @endif

                <div class="mb-4">
                    <x-input-label for="image" :value="$post->image_path ? 'Replace image (optional)' : 'Add an image (optional)'" />
                    <input id="image" name="image" type="file"
                           class="form-control @error('image') is-invalid @enderror"
                           accept="image/png,image/jpeg,image/webp" aria-describedby="imageHelp">
                    <div id="imageHelp" class="form-text">JPG, PNG or WebP, up to 2 MB.</div>
                    <x-input-error :messages="$errors->get('image')" />
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <x-primary-button><i class="bi bi-check-lg" aria-hidden="true"></i> Save changes</x-primary-button>
                    <a class="btn btn-outline-primary" href="{{ route('posts.show', $post) }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
