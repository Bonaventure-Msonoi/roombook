@props([
    'class' => 'w-auto max-w-[min(100%,360px)] h-auto max-h-14 md:max-h-16 object-contain',
])

@php
    $logoPath = public_path('images/logo/prudential-horizontal.png');
    $logoVersion = is_file($logoPath) ? filemtime($logoPath) : time();
@endphp

<img src="/images/logo/prudential-horizontal.png?v={{ $logoVersion }}"
     alt="Prudential"
     width="360"
     height="88"
     {{ $attributes->merge(['class' => $class]) }}
     decoding="async">