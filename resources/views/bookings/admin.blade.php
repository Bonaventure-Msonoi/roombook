<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('All Bookings (Admin)') }}
            </h2>
            @if (Auth::user()->canBookOnBehalf())
                <a href="{{ route('boardroom.bookings.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                    Book on behalf
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if ($bookings->isEmpty())
                    <div class="p-6 text-gray-500 text-sm">No bookings found.</div>
                @else
                    <div class="md:hidden divide-y divide-gray-200">
                        @foreach ($bookings as $booking)
                            <div class="p-4 space-y-3">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $booking->title }}</p>
                                    <p class="text-sm text-gray-600">{{ $booking->room->name }}</p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ $booking->starts_at->format('M j, g:i A') }}
                                        – {{ $booking->ends_at->format('g:i A') }}
                                    </p>
                                    <p class="text-sm text-gray-500">Booked by {{ $booking->user->name }}</p>
                                </div>
                                @if ($booking->ends_at->isFuture())
                                    <div class="flex items-center gap-4">
                                        @if (Auth::user()->canManageAnyBooking() || $booking->user_id === Auth::id())
                                            <a href="{{ route('boardroom.bookings.edit', $booking) }}" class="text-sm text-indigo-600 hover:text-indigo-800">Edit</a>
                                        @endif
                                        @if ($booking->user_id === Auth::id() || Auth::user()->canCancelAnyBooking())
                                            <form method="POST" action="{{ route('boardroom.bookings.destroy', $booking) }}"
                                                  onsubmit="return confirm('Cancel this booking?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm text-red-600 hover:text-red-800">Cancel</button>
                                            </form>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="hidden md:block overflow-x-auto">
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
                                        <td class="px-6 py-4 text-right space-x-3 whitespace-nowrap">
                                            @if ($booking->ends_at->isFuture())
                                                @if (Auth::user()->canManageAnyBooking() || $booking->user_id === Auth::id())
                                                    <a href="{{ route('boardroom.bookings.edit', $booking) }}" class="text-sm text-indigo-600 hover:text-indigo-800">Edit</a>
                                                @endif
                                                @if ($booking->user_id === Auth::id() || Auth::user()->canCancelAnyBooking())
                                                    <form method="POST" action="{{ route('boardroom.bookings.destroy', $booking) }}" class="inline"
                                                          onsubmit="return confirm('Cancel this booking?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-sm text-red-600 hover:text-red-800">Cancel</button>
                                                    </form>
                                                @endif
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
