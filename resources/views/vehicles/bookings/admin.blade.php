<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">{{ __('All Vehicle Bookings') }}</h2></x-slot>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vehicle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purpose</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Booked by</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">When</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($bookings as $booking)
                            <tr>
                                <td class="px-6 py-4 text-sm">{{ $booking->vehicle->displayName() }}</td>
                                <td class="px-6 py-4 text-sm">{{ $booking->title }}</td>
                                <td class="px-6 py-4 text-sm">{{ $booking->user->name }}</td>
                                <td class="px-6 py-4 text-sm">{{ $booking->starts_at->format('M j, g:i A') }} – {{ $booking->ends_at->format('g:i A') }}</td>
                                <td class="px-6 py-4 text-right">
                                    @if ($booking->ends_at->isFuture())
                                        <form method="POST" action="{{ route('vehicles.bookings.destroy', $booking) }}" class="inline" onsubmit="return confirm('Cancel?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-sm text-red-600">Cancel</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No bookings.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
