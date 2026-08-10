@props([
    'href',
    'title',
    'description',
    'variant' => 'red',
])

@php
    $styles = match ($variant) {
        'emerald' => [
            'border' => 'border-brand-emerald-200 hover:border-brand-emerald',
            'iconBg' => 'bg-brand-emerald-100 text-brand-emerald-700 group-hover:bg-brand-emerald group-hover:text-brand-emerald-900',
            'accent' => 'from-brand-emerald-50 to-white',
        ],
        'turquoise' => [
            'border' => 'border-brand-turquoise-200 hover:border-brand-turquoise',
            'iconBg' => 'bg-brand-turquoise-100 text-brand-turquoise-700 group-hover:bg-brand-turquoise group-hover:text-brand-turquoise-900',
            'accent' => 'from-brand-turquoise-50 to-white',
        ],
        'orange' => [
            'border' => 'border-brand-orange-200 hover:border-brand-orange',
            'iconBg' => 'bg-brand-orange-100 text-brand-orange-700 group-hover:bg-brand-orange group-hover:text-white',
            'accent' => 'from-brand-orange-50 to-white',
        ],
        default => [
            'border' => 'border-brand-red-100 hover:border-brand-red',
            'iconBg' => 'bg-brand-red-100 text-brand-red group-hover:bg-brand-red group-hover:text-white',
            'accent' => 'from-brand-soft-50 to-white',
        ],
    };
@endphp

<a href="{{ $href }}"
   {{ $attributes->merge([
       'class' => "group block rounded-xl border-2 bg-gradient-to-br {$styles['accent']} p-6 sm:p-8 shadow-sm hover:shadow-md transition {$styles['border']}",
   ]) }}>
    <div class="flex h-14 w-14 items-center justify-center rounded-full mb-4 transition {{ $styles['iconBg'] }}">
        {{ $icon }}
    </div>
    <h3 class="text-xl font-bold text-gray-900">{{ $title }}</h3>
    <p class="mt-2 text-sm text-gray-600">{{ $description }}</p>
</a>
