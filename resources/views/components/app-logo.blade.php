@props(['href' => route('home'), 'size' => 'md'])

@php
    $sizes = [
        'sm' => 'h-8 w-8',
        'md' => 'h-10 w-10',
        'lg' => 'h-24 w-24',
    ];
    $class = $sizes[$size] ?? $sizes['md'];
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex items-center']) }}>
    <img src="{{ asset('images/logo/reservio-logo.png') }}" alt="ReserVio" class="{{ $class }} object-contain" />
</a>
