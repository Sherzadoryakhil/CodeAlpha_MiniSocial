<x-guest-layout title="Log in" aside="login">
    <h1 class="h4">Log in</h1>
    <p class="auth-lead">Welcome back! Enter your details to continue.</p>

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')"
                          required autofocus autocomplete="username" placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mb-3">
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" type="password" name="password"
                          required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="form-check mb-3">
            <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
            <label for="remember_me" class="form-check-label">Remember me</label>
        </div>

        <div class="d-grid">
            <x-primary-button>Log in</x-primary-button>
        </div>

        <p class="auth-switch">
            New to MiniSocial? <a href="{{ route('register') }}">Create an account</a>
        </p>
    </form>
</x-guest-layout>
