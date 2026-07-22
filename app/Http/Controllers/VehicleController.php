<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(): View
    {
        $vehicles = Vehicle::active()
            ->withCount([
                'bookings as upcoming_bookings_count' => function ($query) {
                    $query->confirmed()->where('ends_at', '>', now());
                },
            ])
            ->orderBy('make')
            ->get();

        return view('vehicles.index', compact('vehicles'));
    }

    public function show(Request $request, Vehicle $vehicle): View
    {
        $date = $request->filled('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : now()->startOfDay();

        $bookings = VehicleBooking::with('user')
            ->where('vehicle_id', $vehicle->id)
            ->confirmed()
            ->where('starts_at', '<', $date->copy()->endOfDay())
            ->where('ends_at', '>', $date)
            ->orderBy('starts_at')
            ->get();

        return view('vehicles.show', compact('vehicle', 'date', 'bookings'));
    }
}
