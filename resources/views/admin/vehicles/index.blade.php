<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">{{ __('Manage Vehicles') }}</h2>
            <a href="{{ route('admin.vehicles.create') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">Add vehicle</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))<div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>@endif
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plate</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Make</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Year</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($vehicles as $vehicle)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium">{{ $vehicle->number_plate }}</td>
                                <td class="px-4 py-3 text-sm">{{ $vehicle->make }}</td>
                                <td class="px-4 py-3 text-sm">{{ $vehicle->category }}</td>
                                <td class="px-4 py-3 text-sm">{{ $vehicle->year_of_make }}</td>
                                <td class="px-4 py-3 text-sm">{{ $vehicle->is_active ? 'Active' : 'Inactive' }}</td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <a href="{{ route('admin.vehicles.edit', $vehicle) }}" class="text-indigo-600 text-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.vehicles.destroy', $vehicle) }}" class="inline" onsubmit="return confirm('Delete this vehicle?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 text-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No vehicles yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
