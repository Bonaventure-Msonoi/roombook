<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function availability(Request $request): JsonResponse
    {
        $request->validate([
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        $startsAt = Carbon::parse($request->input('starts_at'));
        $endsAt = Carbon::parse($request->input('ends_at'));

        $rooms = Room::orderBy('name')->get()->map(fn (Room $room) => [
            'id' => $room->id,
            'name' => $room->name,
            'capacity' => $room->capacity,
            'color' => $room->color,
            'available' => ! Booking::hasConflict($room->id, $startsAt, $endsAt),
        ]);

        return response()->json($rooms);
    }

    public function create(Request $request): View
    {
        $rooms = Room::orderBy('name')->get();

        return view('bookings.create', [
            'rooms' => $rooms,
            'selectedRoomId' => $request->integer('room'),
            'selectedDate' => $request->input('date'),
            'selectedStartsAt' => $request->input('starts_at'),
            'selectedEndsAt' => $request->input('ends_at'),
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        Booking::create([
            'room_id' => $request->integer('room_id'),
            'user_id' => $request->user()->id,
            'title' => $request->input('title'),
            'starts_at' => $request->input('starts_at'),
            'ends_at' => $request->input('ends_at'),
            'status' => 'confirmed',
        ]);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Room booked successfully.');
    }

    public function mine(): View
    {
        $bookings = Booking::with('room')
            ->where('user_id', auth()->id())
            ->confirmed()
            ->where('ends_at', '>=', now()->subDay())
            ->orderBy('starts_at')
            ->get();

        return view('bookings.mine', compact('bookings'));
    }

    public function adminIndex(): View
    {
        $bookings = Booking::with(['room', 'user'])
            ->confirmed()
            ->where('ends_at', '>=', now()->subDay())
            ->orderBy('starts_at')
            ->get();

        return view('bookings.admin', compact('bookings'));
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        $user = auth()->user();

        if ($booking->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403);
        }

        $booking->update(['status' => 'cancelled']);

        return back()->with('status', 'Booking cancelled.');
    }
}
