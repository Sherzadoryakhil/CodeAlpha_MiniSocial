@php
    $isRegister = $variant === 'register';
@endphp

<aside class="auth-aside" aria-label="About MiniSocial">
    <div>
        <p class="h2 mb-3">
            {{ $isRegister ? 'Your people are already here.' : 'Good to see you again.' }}
        </p>
        <p class="aside-lead">
            {{ $isRegister
                ? 'Create a free account in under a minute. Share what is happening in your life and keep up with friends, family and the people who inspire you.'
                : 'Pick up where you left off. See what your friends have posted, join the conversation and share something new today.' }}
        </p>
    </div>

    <img class="aside-art"
         src="{{ asset('images/' . ($isRegister ? 'auth-register.svg' : 'auth-login.svg')) }}"
         alt="Illustration of friends sharing photos and messages"
         width="520" height="400" loading="lazy">

    <ul class="aside-features">
        <li>
            <span class="f-icon" aria-hidden="true"><i class="bi bi-images"></i></span>
            <div><strong>Share moments</strong><span class="f-text">Post updates and photos in seconds.</span></div>
        </li>
        <li>
            <span class="f-icon" aria-hidden="true"><i class="bi bi-people-fill"></i></span>
            <div><strong>Follow people you love</strong><span class="f-text">Build a feed made of voices you care about.</span></div>
        </li>
        <li>
            <span class="f-icon" aria-hidden="true"><i class="bi bi-chat-heart-fill"></i></span>
            <div><strong>Talk it over</strong><span class="f-text">Like and comment to keep conversations going.</span></div>
        </li>
    </ul>

    <p class="aside-proof">
        <span class="stack" aria-hidden="true"><span>A</span><span>M</span><span>S</span><span>+</span></span>
        <span>Friendly, simple and free to join.</span>
    </p>
</aside>
