<x-guest-layout title="Forgot password">
    <h1 class="h4">{{ __('Forgot your password?') }}</h1>
    <p class="auth-lead">
        {{ __('No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </p>

    <!-- Session Status -->
    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="d-grid gap-2">
            <x-primary-button>{{ __('Email Password Reset Link') }}</x-primary-button>
            <a class="btn btn-outline-primary" href="{{ route('login') }}">Back to log in</a>
        </div>
    </form>
</x-guest-layout>
