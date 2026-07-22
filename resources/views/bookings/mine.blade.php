<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Bookings') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if ($bookings->isEmpty())
                    <div class="p-6 text-gray-500 text-sm">
                        You have no upcoming bookings.
                        <a href="{{ route('boardroom.bookings.create') }}" class="text-indigo-600 hover:text-indigo-800">Book a room</a>
                    </div>
                @else
                    <div class="divide-y divide-gray-200">
                        @foreach ($bookings as $booking)
                            <div class="p-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $booking->title }}</p>
                                    <p class="text-sm text-gray-600">{{ $booking->room->name }}</p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ $booking->starts_at->format('D, M j, Y g:i A') }}
                                        – {{ $booking->ends_at->format('g:i A') }}
                                    </p>
                                </div>
                                @if ($booking->ends_at->isFuture())
                                    <form method="POST" action="{{ route('boardroom.bookings.destroy', $booking) }}"
                                          onsubmit="return confirm('Cancel this booking?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-danger-button type="submit">Cancel</x-danger-button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
