<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('All Bookings (Admin)') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if ($bookings->isEmpty())
                    <div class="p-6 text-gray-500 text-sm">No bookings found.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Room</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Booked by</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">When</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($bookings as $booking)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->room->name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->title }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ $booking->user->name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            {{ $booking->starts_at->format('M j, g:i A') }}
                                            – {{ $booking->ends_at->format('g:i A') }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            @if ($booking->ends_at->isFuture())
                                                <form method="POST" action="{{ route('bookings.destroy', $booking) }}" class="inline"
                                                      onsubmit="return confirm('Cancel this booking?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800">Cancel</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
