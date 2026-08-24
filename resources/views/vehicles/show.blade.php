<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $vehicle->displayName() }}</h2>
                <p class="text-sm text-gray-500">{{ $vehicle->category }}</p>
            </div>
            <a href="{{ route('vehicles.bookings.create', ['vehicle' => $vehicle->id, 'date' => $date->toDateString()]) }}"
               class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-xs font-semibold uppercase rounded-md hover:bg-emerald-700">
                Book this vehicle
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white rounded-lg shadow-sm p-6 grid gap-2 sm:grid-cols-2 text-sm">
                <p><span class="font-medium">Plate:</span> {{ $vehicle->number_plate }}</p>
                <p><span class="font-medium">Make:</span> {{ $vehicle->make }}</p>
                <p><span class="font-medium">Engine:</span> {{ $vehicle->engine_capacity }}</p>
                <p><span class="font-medium">Category:</span> {{ $vehicle->category }}</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Bookings for {{ $date->format('l, M j, Y') }}</h3>
                    <div class="flex gap-2">
                        <a href="{{ route('vehicles.show', ['vehicle' => $vehicle, 'date' => $date->copy()->subDay()->toDateString()]) }}" class="px-3 py-1 text-sm border rounded-md">Previous</a>
                        <a href="{{ route('vehicles.show', ['vehicle' => $vehicle, 'date' => $date->copy()->addDay()->toDateString()]) }}" class="px-3 py-1 text-sm border rounded-md">Next</a>
                    </div>
                </div>
                @if ($bookings->isEmpty())
                    <p class="text-gray-500 text-sm">Available all day.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($bookings as $booking)
                            <div class="rounded-md border px-4 py-3">
                                <p class="font-medium">{{ $booking->title }}</p>
                                <p class="text-sm text-gray-500">{{ $booking->starts_at->format('g:i A') }} – {{ $booking->ends_at->format('g:i A') }} · {{ $booking->user->name }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
