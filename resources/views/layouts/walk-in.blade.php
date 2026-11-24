<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="walk-in-kiosk">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">

        <title>Customer feedback — PPMZ</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased walk-in-kiosk walk-in-page text-gray-800">
        <header id="walk-in-header" class="walk-in-brand-bar">
            <div class="max-w-3xl mx-auto px-4 py-4 md:py-5 flex flex-col items-center gap-2">
                <x-prudential-logo class="max-h-12 sm:max-h-14 md:max-h-16 w-full max-w-[min(100%,340px)]" />
                <p class="text-xs sm:text-sm text-gray-600 tracking-wide uppercase">Pensions Management Zambia</p>
            </div>
        </header>

        <div class="walk-in-body py-6 md:py-8 px-4 sm:px-6 md:px-8">
            <main class="max-w-2xl mx-auto">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>