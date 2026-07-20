<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function events(Request $request): JsonResponse
    {
        $start = Carbon::parse($request->input('start'));
        $end = Carbon::parse($request->input('end'));

        $events = Booking::with(['room', 'user'])
            ->confirmed()
            ->when($request->filled('room_id'), fn ($query) => $query->where('room_id', $request->integer('room_id')))
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->get()
            ->map(fn (Booking $booking) => [
                'id' => $booking->id,
                'title' => $booking->room->name.' — '.$booking->title,
                'start' => $booking->starts_at->toIso8601String(),
                'end' => $booking->ends_at->toIso8601String(),
                'backgroundColor' => $booking->room->color,
                'borderColor' => $booking->room->color,
                'extendedProps' => [
                    'bookedBy' => $booking->user->name,
                    'room' => $booking->room->name,
                    'canCancel' => $booking->user_id === auth()->id() || auth()->user()->isAdmin(),
                ],
            ]);

        return response()->json($events);
    }

    public function __invoke(): View
    {
        $rooms = Room::orderBy('name')->get();

        $maxBookingDate = now()->addMonths(6);

        return view('dashboard', [
            'rooms' => $rooms,
            'roomsJson' => $rooms->map(fn (Room $room) => [
                'id' => $room->id,
                'name' => $room->name,
                'capacity' => $room->capacity,
                'color' => $room->color,
            ])->values(),
            'maxBookingDate' => $maxBookingDate->format('Y-m-d'),
            'maxBookingMonths' => 6,
        ]);
    }
}
