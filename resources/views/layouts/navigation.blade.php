@php
    $viewedUser = request()->route('user');
    $onOwnProfile = auth()->check()
        && request()->routeIs('profile.show')
        && data_get($viewedUser, 'id') == auth()->id();
@endphp

<header class="topbar">
    <nav class="container topbar-inner" aria-label="Main navigation">
        <a class="brand" href="{{ url('/') }}">
            <span class="brand-mark" aria-hidden="true"><i class="bi bi-chat-heart-fill"></i></span>
            <span>MiniSocial</span>
        </a>

        @auth
            <ul class="topbar-links d-none d-md-flex">
                <li>
                    <a class="topbar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                       href="{{ route('dashboard') }}"
                       @if (request()->routeIs('dashboard')) aria-current="page" @endif>
                        <i class="bi bi-house-door{{ request()->routeIs('dashboard') ? '-fill' : '' }}" aria-hidden="true"></i>
                        Home
                    </a>
                </li>
            </ul>
        @endauth

        <div class="topbar-actions">
            @include('layouts.partials.theme-toggle')

            @auth
                <div class="dropdown">
                    <button class="user-menu-btn dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <x-avatar :user="Auth::user()" :size="32" />
                        <span class="user-name">{{ Auth::user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.show', Auth::user()) }}">
                                <i class="bi bi-person-circle" aria-hidden="true"></i> My profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bi bi-pencil-square" aria-hidden="true"></i> Edit profile
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right" aria-hidden="true"></i> Log out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                <a class="btn btn-outline-primary btn-sm" href="{{ route('login') }}">Log in</a>
                <a class="btn btn-primary btn-sm" href="{{ route('register') }}">Register</a>
            @endauth
        </div>
    </nav>
</header>

@auth
    <nav class="tabbar d-md-none" aria-label="Mobile navigation">
        <a class="tabbar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
           href="{{ route('dashboard') }}"
           @if (request()->routeIs('dashboard')) aria-current="page" @endif>
            <i class="bi bi-house-door{{ request()->routeIs('dashboard') ? '-fill' : '' }}" aria-hidden="true"></i>
            <span>Home</span>
        </a>
        <a class="tabbar-link {{ $onOwnProfile ? 'active' : '' }}"
           href="{{ route('profile.show', Auth::user()) }}"
           @if ($onOwnProfile) aria-current="page" @endif>
            <i class="bi bi-person{{ $onOwnProfile ? '-fill' : '' }}" aria-hidden="true"></i>
            <span>My profile</span>
        </a>
    </nav>
@endauth
