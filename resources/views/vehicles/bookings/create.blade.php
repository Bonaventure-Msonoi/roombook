<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">{{ __('Book a Vehicle') }}</h2></x-slot>
    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('vehicles.bookings.store') }}" class="space-y-6">
                    @csrf
                    <div>
                        <x-input-label for="vehicle_id" :value="__('Vehicle')" />
                        <select id="vehicle_id" name="vehicle_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="">Select a vehicle</option>
                            @foreach ($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" @selected(old('vehicle_id', $selectedVehicleId) == $vehicle->id)>
                                    {{ $vehicle->displayName() }} ({{ $vehicle->category }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('vehicle_id')" />
                    </div>
                    <div>
                        <x-input-label for="title" :value="__('Trip purpose')" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required />
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
                            :value="old('ends_at', $selectedEndsAt ?? ($selectedDate ? $selectedDate.'T17:00' : ''))" required />
                        <x-input-error class="mt-2" :messages="$errors->get('ends_at')" />
                    </div>
                    <div class="flex gap-4">
                        <x-primary-button>Confirm Booking</x-primary-button>
                        <a href="{{ route('vehicles.dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
