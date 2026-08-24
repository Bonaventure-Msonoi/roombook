<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Pool Vehicles') }}</h2>
            <a href="{{ route('vehicles.bookings.create') }}"
               class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700">
                Book a Vehicle
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($vehicles->isEmpty())
                <div class="bg-white rounded-lg shadow-sm p-8 text-center text-gray-500">
                    No vehicles in the pool yet.
                    @if (Auth::user()->canManageVehicles())
                        <a href="{{ route('admin.vehicles.create') }}" class="text-indigo-600 hover:text-indigo-800 block mt-2">Add a vehicle</a>
                    @endif
                </div>
            @else
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($vehicles as $vehicle)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="h-2" style="background-color: {{ $vehicle->color }}"></div>
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $vehicle->make }}</h3>
                                <p class="text-sm text-gray-500">{{ $vehicle->number_plate }}</p>
                                <p class="mt-2 text-sm text-gray-600">{{ $vehicle->category }} · {{ $vehicle->engine_capacity }}</p>
                                <p class="mt-2 text-sm text-gray-500">{{ $vehicle->upcoming_bookings_count }} upcoming booking(s)</p>
                                <div class="mt-6 flex gap-3">
                                    <a href="{{ route('vehicles.show', $vehicle) }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-800">View schedule</a>
                                    <a href="{{ route('vehicles.bookings.create', ['vehicle' => $vehicle->id]) }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-800">Book now</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
