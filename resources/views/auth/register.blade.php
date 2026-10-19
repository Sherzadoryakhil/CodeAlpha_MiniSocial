<x-guest-layout title="Register" aside="register">
    <h1 class="h4">Create your account</h1>
    <p class="auth-lead">Join MiniSocial to share updates and follow people you love.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <x-input-label for="name" value="Display name" />
            <x-text-input id="name" type="text" name="name" :value="old('name')"
                          required autofocus autocomplete="name" maxlength="50" placeholder="Your name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="mb-3">
            <x-input-label for="username" value="Username" />
            <x-text-input id="username" type="text" name="username" :value="old('username')"
                          required autocomplete="username" minlength="3" maxlength="30"
                          placeholder="your_username" aria-describedby="usernameHelp" />
            <div id="usernameHelp" class="form-text">3-30 letters, numbers or underscores.</div>
            <x-input-error :messages="$errors->get('username')" />
        </div>

        <div class="mb-3">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')"
                          required autocomplete="email" placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mb-3">
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" type="password" name="password"
                          required autocomplete="new-password" aria-describedby="passwordHelp" />
            <div id="passwordHelp" class="form-text">At least 8 characters.</div>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="mb-3">
            <x-input-label for="password_confirmation" value="Confirm password" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation"
                          required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="d-grid">
            <x-primary-button>Register</x-primary-button>
        </div>

        <p class="auth-switch">
            Already registered? <a href="{{ route('login') }}">Log in</a>
        </p>
    </form>
</x-guest-layout>
