<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $booking->exists ? __('Edit Room Booking') : __('Book a Room') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST"
                      action="{{ $booking->exists ? route('boardroom.bookings.update', $booking) : route('boardroom.bookings.store') }}"
                      class="space-y-6">
                    @csrf
                    @if ($booking->exists) @method('PUT') @endif

                    <div>
                        <x-input-label for="room_id" :value="__('Room')" />
                        <select id="room_id" name="room_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                            <option value="">Select a room</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}" @selected(old('room_id', $selectedRoomId ?? $booking->room_id) == $room->id)>
                                    {{ $room->name }} ({{ $room->capacity }} seats)
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('room_id')" />
                    </div>

                    <div>
                        <x-input-label for="title" :value="__('Meeting title / purpose')" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                                      :value="old('title', $booking->title)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('title')" />
                    </div>

                    @if ($canReassign)
                        <div>
                            <x-input-label for="user_id" :value="__('Booked for')" />
                            <select id="user_id" name="user_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('user_id', $booking->user_id) == $user->id)>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Reassign this booking to another user.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('user_id')" />
                        </div>
                    @elseif ($canBookOnBehalf)
                        <div>
                            <x-input-label for="user_id" :value="__('Book for (optional)')" />
                            <select id="user_id" name="user_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Myself ({{ Auth::user()->name }})</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Leave as Myself, or choose another user to book on their behalf.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('user_id')" />
                        </div>
                    @endif

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

                    @if ($booking->exists || $canBookOnBehalf)
                        <div>
                            <x-input-label for="reason" :value="__('Reason / note (optional)')" />
                            <x-text-input id="reason" name="reason" type="text" class="mt-1 block w-full"
                                          :value="old('reason')" placeholder="e.g. urgent meeting, owner unavailable" />
                            <x-input-error class="mt-2" :messages="$errors->get('reason')" />
                        </div>
                    @endif

                    <div class="flex items-center gap-4">
                        <x-primary-button>
                            {{ $booking->exists ? __('Save changes') : __('Confirm Booking') }}
                        </x-primary-button>
                        <a href="{{ $booking->exists && Auth::user()->canAccessAdminDashboard() ? route('boardroom.bookings.admin') : route('boardroom.dashboard') }}"
                           class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
