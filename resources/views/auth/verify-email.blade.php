<x-guest-layout title="Verify email">
    <h1 class="h4">Verify your email</h1>
    <p class="auth-lead">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success" role="status">
            <i class="bi bi-check-circle-fill alert-icon" aria-hidden="true"></i>
            <div class="alert-body">{{ __('A new verification link has been sent to the email address you provided during registration.') }}</div>
        </div>
    @endif

    <div class="auth-links">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>{{ __('Resend Verification Email') }}</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-primary">{{ __('Log Out') }}</button>
        </form>
    </div>
</x-guest-layout>
