@props(['level' => 3])

@php
    $emoji = \App\Models\WalkInFeedback::SATISFACTION_EMOJIS[$level] ?? '😐';
@endphp

<div {{ $attributes->merge(['class' => "walk-in-mood walk-in-mood--{$level}"]) }}>
    <span class="walk-in-mood-face" aria-hidden="true">{{ $emoji }}</span>
    @if ($level === 1)
        <span class="walk-in-mood-tear walk-in-mood-tear--a" aria-hidden="true"></span>
        <span class="walk-in-mood-tear walk-in-mood-tear--b" aria-hidden="true"></span>
    @endif
</div>