<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">{{ __('My Vehicle Bookings') }}</h2></x-slot>
    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))<div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>@endif
            <div class="bg-white shadow-sm sm:rounded-lg">
                @if ($bookings->isEmpty())
                    <div class="p-6 text-gray-500 text-sm">No vehicle bookings. <a href="{{ route('vehicles.bookings.create') }}" class="text-emerald-600">Book a vehicle</a></div>
                @else
                    <div class="divide-y">
                        @foreach ($bookings as $booking)
                            <div class="p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <p class="font-semibold">{{ $booking->title }}</p>
                                    <p class="text-sm text-gray-600">{{ $booking->vehicle->displayName() }}</p>
                                    <p class="text-sm text-gray-500">{{ $booking->starts_at->format('D, M j, Y g:i A') }} – {{ $booking->ends_at->format('g:i A') }}</p>
                                </div>
                                @if ($booking->ends_at->isFuture())
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('vehicles.bookings.edit', $booking) }}" class="text-sm text-indigo-600 hover:text-indigo-800">Edit</a>
                                        <form method="POST" action="{{ route('vehicles.bookings.destroy', $booking) }}" onsubmit="return confirm('Cancel this booking?')">
                                            @csrf @method('DELETE')
                                            <x-danger-button type="submit">Cancel</x-danger-button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
