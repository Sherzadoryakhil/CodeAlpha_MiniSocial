@php
    $aside = $attributes->get('aside');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">
    <title>{{ $attributes->get('title') ? $attributes->get('title') . ' - ' : '' }}{{ config('app.name', 'MiniSocial') }}</title>
    @include('layouts.partials.theme-init')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="visually-hidden-focusable skip-link" href="#main-content">Skip to main content</a>

    <div class="auth-shell {{ $aside ? 'has-aside' : '' }}">
        <main id="main-content" class="auth-main">
            <div class="auth-top">
                <a href="{{ url('/') }}" class="brand">
                    <span class="brand-mark" aria-hidden="true"><i class="bi bi-chat-heart-fill"></i></span>
                    <span>MiniSocial</span>
                </a>
                @include('layouts.partials.theme-toggle')
            </div>

            <div class="auth-card">
                {{ $slot }}
            </div>
        </main>

        @if ($aside)
            @include('layouts.partials.auth-aside', ['variant' => $aside])
        @endif
    </div>
</body>
</html>
