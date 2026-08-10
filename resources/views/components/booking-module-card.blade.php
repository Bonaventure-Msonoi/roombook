@props([
    'href',
    'title',
    'description',
    'variant' => 'indigo',
])

@php
    $styles = match ($variant) {
        'emerald' => [
            'border' => 'border-emerald-100 hover:border-emerald-500',
            'iconBg' => 'bg-emerald-100 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white',
        ],
        default => [
            'border' => 'border-indigo-100 hover:border-indigo-500',
            'iconBg' => 'bg-indigo-100 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white',
        ],
    };
@endphp

<a href="{{ $href }}"
   {{ $attributes->merge([
       'class' => "group block rounded-xl border-2 bg-white p-6 sm:p-8 shadow-sm hover:shadow-md transition {$styles['border']}",
   ]) }}>
    <div class="flex h-14 w-14 items-center justify-center rounded-full mb-4 transition {{ $styles['iconBg'] }}">
        {{ $icon }}
    </div>
    <h3 class="text-xl font-bold text-gray-900">{{ $title }}</h3>
    <p class="mt-2 text-sm text-gray-600">{{ $description }}</p>
</a>
