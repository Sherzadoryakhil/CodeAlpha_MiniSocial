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
<body class="@auth has-tabbar @endauth">
    <a class="visually-hidden-focusable skip-link" href="#main-content">Skip to main content</a>

    @include('layouts.navigation')

    @isset($header)
        <header class="page-header">
            <div class="container py-3">{{ $header }}</div>
        </header>
    @endisset

    <main id="main-content" class="container app-main">
        @if (session('success') || session('status'))
            <div class="alert alert-success alert-dismissible fade show page-col" role="status">
                <i class="bi bi-check-circle-fill alert-icon" aria-hidden="true"></i>
                <div class="alert-body"><strong>Success:</strong> {{ session('success') ?? session('status') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close message"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show page-col" role="alert">
                <i class="bi bi-exclamation-triangle-fill alert-icon" aria-hidden="true"></i>
                <div class="alert-body"><strong>Error:</strong> {{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close message"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger page-col" role="alert">
                <i class="bi bi-exclamation-triangle-fill alert-icon" aria-hidden="true"></i>
                <div class="alert-body"><strong>Error:</strong> Please fix the highlighted fields below and try again.</div>
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
