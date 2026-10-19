@props(['disabled' => false])

@php
    $invalid = $attributes->has('name') && $errors->has($attributes->get('name'));
@endphp

<input @disabled($disabled) {{ $attributes->merge(['class' => 'form-control' . ($invalid ? ' is-invalid' : '')]) }}>
