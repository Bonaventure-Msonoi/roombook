<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Book a Room') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('boardroom.bookings.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="room_id" :value="__('Room')" />
                        <select id="room_id" name="room_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                            <option value="">Select a room</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}" @selected(old('room_id', $selectedRoomId) == $room->id)>
                                    {{ $room->name }} ({{ $room->capacity }} seats)
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('room_id')" />
                    </div>

                    <div>
                        <x-input-label for="title" :value="__('Meeting title / purpose')" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                                      :value="old('title')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('title')" />
                    </div>

                    <div>
                        <x-input-label for="starts_at" :value="__('Start time')" />
                        <x-text-input id="starts_at" name="starts_at" type="datetime-local" class="mt-1 block w-full"
                                      :value="old('starts_at', $selectedStartsAt ?? ($selectedDate ? $selectedDate.'T09:00' : ''))" required />
                        <x-input-error class="mt-2" :messages="$errors->get('starts_at')" />
                    </div>

                    <div>
                        <x-input-label for="ends_at" :value="__('End time')" />
                        <x-text-input id="ends_at" name="ends_at" type="datetime-local" class="mt-1 block w-full"
                                      :value="old('ends_at', $selectedEndsAt ?? ($selectedDate ? $selectedDate.'T10:00' : ''))" required />
                        <x-input-error class="mt-2" :messages="$errors->get('ends_at')" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Confirm Booking') }}</x-primary-button>
                        <a href="{{ route('boardroom.dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
