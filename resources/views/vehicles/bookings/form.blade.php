<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            {{ $booking->exists ? __('Edit Vehicle Booking') : __('Book a Vehicle') }}
        </h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST"
                      action="{{ $booking->exists ? route('vehicles.bookings.update', $booking) : route('vehicles.bookings.store') }}"
                      class="space-y-6">
                    @csrf
                    @if ($booking->exists) @method('PUT') @endif

                    <div>
                        <x-input-label for="vehicle_id" :value="__('Vehicle')" />
                        <select id="vehicle_id" name="vehicle_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="">Select a vehicle</option>
                            @foreach ($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" @selected(old('vehicle_id', $selectedVehicleId ?? $booking->vehicle_id) == $vehicle->id)>
                                    {{ $vehicle->displayName() }} ({{ $vehicle->category }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('vehicle_id')" />
                    </div>

                    <div>
                        <x-input-label for="title" :value="__('Trip purpose')" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                                      :value="old('title', $booking->title)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('title')" />
                    </div>

                    <div>
                        <x-input-label for="destination" :value="__('Destination (location)')" />
                        <x-text-input id="destination" name="destination" type="text" class="mt-1 block w-full"
                                      :value="old('destination', $booking->destination)" required
                                      placeholder="e.g. Airport, HQ campus" />
                        <x-input-error class="mt-2" :messages="$errors->get('destination')" />
                    </div>

                    @if ($canReassign)
                        <div>
                            <x-input-label for="user_id" :value="__('Booked for')" />
                            <select id="user_id" name="user_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
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
                            <select id="user_id" name="user_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
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
                            :value="old('ends_at', $selectedEndsAt ?? ($selectedDate ? $selectedDate.'T17:00' : ''))" required />
                        <x-input-error class="mt-2" :messages="$errors->get('ends_at')" />
                    </div>
                    <p id="booking-duration-label" class="text-sm font-medium text-gray-800"></p>

                    @if ($booking->exists || $canBookOnBehalf)
                        <div>
                            <x-input-label for="reason" :value="__('Reason / note (optional)')" />
                            <x-text-input id="reason" name="reason" type="text" class="mt-1 block w-full"
                                          :value="old('reason')" placeholder="e.g. driver change, urgent trip" />
                            <x-input-error class="mt-2" :messages="$errors->get('reason')" />
                        </div>
                    @endif

                    <div class="flex gap-4">
                        <x-primary-button>{{ $booking->exists ? 'Save changes' : 'Confirm Booking' }}</x-primary-button>
                        <a href="{{ $booking->exists && Auth::user()->canAccessAdminDashboard() ? route('vehicles.bookings.admin') : route('vehicles.dashboard') }}"
                           class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                const startInput = document.getElementById('starts_at');
                const endInput = document.getElementById('ends_at');
                const label = document.getElementById('booking-duration-label');
                if (!startInput || !endInput || !label) return;

                let durationMinutes = null;

                function parseLocal(value) {
                    if (!value) return null;
                    const [datePart, timePart] = value.split('T');
                    if (!datePart || !timePart) return null;
                    const [year, month, day] = datePart.split('-').map(Number);
                    const [hour, minute] = timePart.split(':').map(Number);
                    return new Date(year, month - 1, day, hour, minute || 0, 0);
                }

                function toLocal(date) {
                    const pad = (n) => String(n).padStart(2, '0');
                    return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate()) +
                        'T' + pad(date.getHours()) + ':' + pad(date.getMinutes());
                }

                function syncLabel() {
                    const start = parseLocal(startInput.value);
                    const end = parseLocal(endInput.value);
                    if (!start || !end) {
                        label.textContent = '';
                        durationMinutes = null;
                        return;
                    }
                    durationMinutes = Math.round((end - start) / 60000);
                    if (durationMinutes <= 0) {
                        label.textContent = '';
                        return;
                    }
                    const hours = Math.floor(durationMinutes / 60);
                    const mins = durationMinutes % 60;
                    if (hours && mins) label.textContent = `Duration: ${hours}h ${mins}m`;
                    else if (hours) label.textContent = `Duration: ${hours} hour${hours === 1 ? '' : 's'}`;
                    else label.textContent = `Duration: ${mins} minute${mins === 1 ? '' : 's'}`;
                }

                startInput.addEventListener('change', () => {
                    const start = parseLocal(startInput.value);
                    if (start && durationMinutes && durationMinutes > 0) {
                        endInput.value = toLocal(new Date(start.getTime() + durationMinutes * 60000));
                    }
                    syncLabel();
                });
                endInput.addEventListener('change', syncLabel);
                syncLabel();
            })();
        </script>
    @endpush
</x-app-layout>
