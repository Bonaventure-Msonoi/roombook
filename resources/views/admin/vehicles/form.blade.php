<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ $vehicle->exists ? __('Edit Vehicle') : __('Add Vehicle') }}</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ $vehicle->exists ? route('admin.vehicles.update', $vehicle) : route('admin.vehicles.store') }}" class="space-y-4">
                    @csrf
                    @if ($vehicle->exists) @method('PUT') @endif

                    <div>
                        <x-input-label for="number_plate" value="Number plate" />
                        <x-text-input id="number_plate" name="number_plate" class="mt-1 block w-full" :value="old('number_plate', $vehicle->number_plate)" required />
                        <x-input-error :messages="$errors->get('number_plate')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="chassis_number" value="Chassis number" />
                        <x-text-input id="chassis_number" name="chassis_number" class="mt-1 block w-full" :value="old('chassis_number', $vehicle->chassis_number)" required />
                        <x-input-error :messages="$errors->get('chassis_number')" class="mt-1" />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="make" value="Make" />
                            <x-text-input id="make" name="make" class="mt-1 block w-full" :value="old('make', $vehicle->make)" required />
                            <x-input-error :messages="$errors->get('make')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="year_of_make" value="Year of make" />
                            <x-text-input id="year_of_make" name="year_of_make" type="number" class="mt-1 block w-full" :value="old('year_of_make', $vehicle->year_of_make)" required />
                            <x-input-error :messages="$errors->get('year_of_make')" class="mt-1" />
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="engine_capacity" value="Engine capacity" />
                            <x-text-input id="engine_capacity" name="engine_capacity" class="mt-1 block w-full" placeholder="e.g. 2000cc" :value="old('engine_capacity', $vehicle->engine_capacity)" required />
                            <x-input-error :messages="$errors->get('engine_capacity')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="category" value="Category" />
                            <select id="category" name="category" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category }}" @selected(old('category', $vehicle->category) === $category)>{{ $category }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category')" class="mt-1" />
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="color" value="Calendar colour" />
                            <x-text-input id="color" name="color" type="color" class="mt-1 block w-full h-10" :value="old('color', $vehicle->color ?? '#6366f1')" required />
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $vehicle->is_active)) class="rounded border-gray-300">
                                <span class="text-sm text-gray-700">Active in pool</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex gap-4 pt-2">
                        <x-primary-button>{{ $vehicle->exists ? 'Update' : 'Add vehicle' }}</x-primary-button>
                        <a href="{{ route('admin.vehicles.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
