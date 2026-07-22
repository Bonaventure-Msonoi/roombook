<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Rooms') }}
            </h2>
            <a href="{{ route('boardroom.bookings.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                Book a Room
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($rooms as $room)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="h-2" style="background-color: {{ $room->color }}"></div>
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $room->name }}</h3>
                            <p class="mt-2 text-sm text-gray-600">{{ $room->description }}</p>
                            <p class="mt-4 text-sm text-gray-500">Capacity: {{ $room->capacity }} people</p>
                            <p class="text-sm text-gray-500">{{ $room->upcoming_bookings_count }} upcoming booking(s)</p>
                            <div class="mt-6 flex gap-3">
                                <a href="{{ route('boardroom.rooms.show', $room) }}"
                                   class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                    View schedule
                                </a>
                                <a href="{{ route('boardroom.bookings.create', ['room' => $room->id]) }}"
                                   class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                    Book now
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
