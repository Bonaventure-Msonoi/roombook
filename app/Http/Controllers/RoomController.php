<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(): View
    {
        $rooms = Room::withCount([
            'bookings as upcoming_bookings_count' => function ($query) {
                $query->confirmed()->where('ends_at', '>', now());
            },
        ])->orderBy('name')->get();

        return view('rooms.index', compact('rooms'));
    }

    public function show(Request $request, Room $room): View
    {
        $date = $request->filled('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : now()->startOfDay();

        $bookings = Booking::with('user')
            ->where('room_id', $room->id)
            ->confirmed()
            ->where('starts_at', '<', $date->copy()->endOfDay())
            ->where('ends_at', '>', $date)
            ->orderBy('starts_at')
            ->get();

        return view('rooms.show', [
            'room' => $room,
            'date' => $date,
            'bookings' => $bookings,
        ]);
    }
}
