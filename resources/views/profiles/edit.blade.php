<x-app-layout title="Edit profile">
    <div class="page-col page-col--form">
        <p class="mb-2"><a class="back-link" href="{{ route('profile.show', $user) }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to profile</a></p>

        <div class="card card-pad">
            <h1 class="h4 mb-4">Edit profile</h1>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="d-flex align-items-start gap-3 mb-3">
                    <x-avatar :user="$user" :size="80" alt="Your current profile photo" />
                    <div class="flex-grow-1 min-w-0">
                        <x-input-label for="photo" value="Profile photo" />
                        <input id="photo" name="photo" type="file"
                               class="form-control @error('photo') is-invalid @enderror"
                               accept="image/png,image/jpeg,image/webp" aria-describedby="photoHelp">
                        <div id="photoHelp" class="form-text">JPG, PNG or WebP, up to 2 MB.</div>
                        <x-input-error :messages="$errors->get('photo')" />
                    </div>
                </div>

                <div class="mb-3">
                    <x-input-label for="name" value="Display name" />
                    <x-text-input id="name" type="text" name="name" :value="old('name', $user->name)"
                                  required maxlength="50" autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div class="mb-3">
                    <x-input-label for="username" value="Username" />
                    <x-text-input id="username" type="text" name="username" :value="old('username', $user->username)"
                                  required minlength="3" maxlength="30" autocomplete="username"
                                  aria-describedby="usernameHelp" />
                    <div id="usernameHelp" class="form-text">3-30 letters, numbers or underscores.</div>
                    <x-input-error :messages="$errors->get('username')" />
                </div>

                <div class="mb-3">
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" type="email" name="email" :value="old('email', $user->email)"
                                  required autocomplete="email" />
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <div class="mb-4">
                    <x-input-label for="bio" value="Bio" />
                    <textarea id="bio" name="bio" rows="3" maxlength="160"
                              class="form-control @error('bio') is-invalid @enderror"
                              aria-describedby="bioHelp"
                              placeholder="Tell people a little about yourself">{{ old('bio', $user->bio) }}</textarea>
                    <div id="bioHelp" class="form-text">Up to 160 characters.</div>
                    <x-input-error :messages="$errors->get('bio')" />
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <x-primary-button><i class="bi bi-check-lg" aria-hidden="true"></i> Save changes</x-primary-button>
                    <a class="btn btn-outline-primary" href="{{ route('profile.show', $user) }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
