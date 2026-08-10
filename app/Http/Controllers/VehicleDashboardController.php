<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleBooking;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleDashboardController extends Controller
{
    public function events(Request $request): JsonResponse
    {
        $start = Carbon::parse($request->input('start'));
        $end = Carbon::parse($request->input('end'));

        $events = VehicleBooking::with(['vehicle', 'user'])
            ->confirmed()
            ->when($request->filled('vehicle_id'), fn ($query) => $query->where('vehicle_id', $request->integer('vehicle_id')))
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->get()
            ->map(fn (VehicleBooking $booking) => [
                'id' => $booking->id,
                'title' => $booking->vehicle->displayName().' — '.$booking->title,
                'start' => $booking->starts_at->toIso8601String(),
                'end' => $booking->ends_at->toIso8601String(),
                'backgroundColor' => $booking->vehicle->color,
                'borderColor' => $booking->vehicle->color,
                'extendedProps' => [
                    'bookedBy' => $booking->user->name,
                    'vehicle' => $booking->vehicle->displayName(),
                    'destination' => $booking->destination,
                    'canCancel' => $booking->user_id === auth()->id() || auth()->user()->canCancelAnyBooking(),
                ],
            ]);

        return response()->json($events);
    }

    public function __invoke(): View
    {
        $vehicles = Vehicle::active()->orderBy('make')->get();
        $maxBookingDate = now()->addMonths(6);

        return view('vehicles.dashboard', [
            'vehicles' => $vehicles,
            'vehiclesJson' => $vehicles->map(fn (Vehicle $vehicle) => [
                'id' => $vehicle->id,
                'name' => $vehicle->displayName(),
                'category' => $vehicle->category,
                'color' => $vehicle->color,
            ])->values(),
            'maxBookingDate' => $maxBookingDate->format('Y-m-d'),
            'maxBookingMonths' => 6,
        ]);
    }
}
