<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $room->name }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Capacity: {{ $room->capacity }} people</p>
            </div>
            <a href="{{ route('boardroom.bookings.create', ['room' => $room->id, 'date' => $date->toDateString()]) }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                Book this room
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-gray-700">{{ $room->description }}</p>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Bookings for {{ $date->format('l, M j, Y') }}
                    </h3>
                    <div class="flex gap-2">
                        <a href="{{ route('boardroom.rooms.show', ['room' => $room, 'date' => $date->copy()->subDay()->toDateString()]) }}"
                           class="px-3 py-1 text-sm border rounded-md text-gray-600 hover:bg-gray-50">Previous</a>
                        <a href="{{ route('boardroom.rooms.show', ['room' => $room, 'date' => $date->copy()->addDay()->toDateString()]) }}"
                           class="px-3 py-1 text-sm border rounded-md text-gray-600 hover:bg-gray-50">Next</a>
                    </div>
                </div>

                @if ($bookings->isEmpty())
                    <p class="text-gray-500 text-sm">No bookings on this day. The room is available.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($bookings as $booking)
                            <div class="flex items-center justify-between rounded-md border border-gray-200 px-4 py-3">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $booking->title }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ $booking->starts_at->format('g:i A') }} – {{ $booking->ends_at->format('g:i A') }}
                                        · {{ $booking->user->name }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
