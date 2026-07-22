<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Booking Portal') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <p class="text-gray-600 mb-8 text-center">Choose what you would like to book</p>

            <div class="grid gap-6 md:grid-cols-2">
                <a href="{{ route('boardroom.dashboard') }}"
                   class="group block rounded-xl border-2 border-indigo-100 bg-white p-8 shadow-sm hover:border-indigo-500 hover:shadow-md transition">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 mb-4 group-hover:bg-indigo-600 group-hover:text-white transition">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Book Boardroom</h3>
                    <p class="mt-2 text-sm text-gray-600">Reserve meeting rooms and boardrooms for your team.</p>
                </a>

                <a href="{{ route('vehicles.dashboard') }}"
                   class="group block rounded-xl border-2 border-emerald-100 bg-white p-8 shadow-sm hover:border-emerald-500 hover:shadow-md transition">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 mb-4 group-hover:bg-emerald-600 group-hover:text-white transition">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m-8 4h8m-4 4H8M5 7h.01M5 11h.01M5 15h.01M17 17H7l-1 4h12l-1-4z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Pool Vehicle</h3>
                    <p class="mt-2 text-sm text-gray-600">Book pool vehicles for official trips and errands.</p>
                </a>
            </div>

            @if (Auth::user()->isSuperUser())
                <div class="mt-10 rounded-lg border border-amber-200 bg-amber-50 p-6">
                    <h4 class="font-semibold text-amber-900">Super user tools</h4>
                    <p class="text-sm text-amber-800 mt-1">Manage the vehicle pool and view all system bookings.</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('admin.vehicles.index') }}"
                           class="inline-flex items-center px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-md hover:bg-amber-700">
                            Manage vehicles
                        </a>
                        <a href="{{ route('boardroom.bookings.admin') }}"
                           class="inline-flex items-center px-4 py-2 border border-amber-300 text-amber-900 text-sm font-medium rounded-md hover:bg-amber-100">
                            Boardroom bookings
                        </a>
                        <a href="{{ route('vehicles.bookings.admin') }}"
                           class="inline-flex items-center px-4 py-2 border border-amber-300 text-amber-900 text-sm font-medium rounded-md hover:bg-amber-100">
                            Vehicle bookings
                        </a>
                    </div>
                </div>
            @elseif (Auth::user()->isAdmin())
                <div class="mt-10 flex flex-wrap gap-3 justify-center">
                    <a href="{{ route('boardroom.bookings.admin') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Boardroom admin</a>
                    <span class="text-gray-300">|</span>
                    <a href="{{ route('vehicles.bookings.admin') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Vehicle admin</a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
