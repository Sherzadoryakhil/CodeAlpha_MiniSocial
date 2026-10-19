@props(['user', 'size' => 40, 'alt' => ''])

<img src="{{ $user->avatar_url }}" alt="{{ $alt }}" width="{{ $size }}" height="{{ $size }}"
     {{ $attributes->merge(['class' => 'rounded-circle object-fit-cover flex-shrink-0']) }}>
