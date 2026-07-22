<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleBookingRequest;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleBookingController extends Controller
{
    public function availability(Request $request): JsonResponse
    {
        $request->validate([
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        $startsAt = Carbon::parse($request->input('starts_at'));
        $endsAt = Carbon::parse($request->input('ends_at'));

        $vehicles = Vehicle::active()->orderBy('make')->get()->map(fn (Vehicle $vehicle) => [
            'id' => $vehicle->id,
            'name' => $vehicle->displayName(),
            'category' => $vehicle->category,
            'color' => $vehicle->color,
            'available' => ! VehicleBooking::hasConflict($vehicle->id, $startsAt, $endsAt),
        ]);

        return response()->json($vehicles);
    }

    public function create(Request $request): View
    {
        $vehicles = Vehicle::active()->orderBy('make')->get();

        return view('vehicles.bookings.create', [
            'vehicles' => $vehicles,
            'selectedVehicleId' => $request->integer('vehicle'),
            'selectedDate' => $request->input('date'),
            'selectedStartsAt' => $request->input('starts_at'),
            'selectedEndsAt' => $request->input('ends_at'),
        ]);
    }

    public function store(StoreVehicleBookingRequest $request): RedirectResponse
    {
        VehicleBooking::create([
            'vehicle_id' => $request->integer('vehicle_id'),
            'user_id' => $request->user()->id,
            'title' => $request->input('title'),
            'starts_at' => $request->input('starts_at'),
            'ends_at' => $request->input('ends_at'),
            'status' => 'confirmed',
        ]);

        return redirect()
            ->route('vehicles.dashboard')
            ->with('status', 'Vehicle booked successfully.');
    }

    public function mine(): View
    {
        $bookings = VehicleBooking::with('vehicle')
            ->where('user_id', auth()->id())
            ->confirmed()
            ->where('ends_at', '>=', now()->subDay())
            ->orderBy('starts_at')
            ->get();

        return view('vehicles.bookings.mine', compact('bookings'));
    }

    public function adminIndex(): View
    {
        $bookings = VehicleBooking::with(['vehicle', 'user'])
            ->confirmed()
            ->where('ends_at', '>=', now()->subDay())
            ->orderBy('starts_at')
            ->get();

        return view('vehicles.bookings.admin', compact('bookings'));
    }

    public function destroy(VehicleBooking $vehicleBooking): RedirectResponse
    {
        $user = auth()->user();

        if ($vehicleBooking->user_id !== $user->id && ! $user->canCancelAnyBooking()) {
            abort(403);
        }

        $vehicleBooking->update(['status' => 'cancelled']);

        return back()->with('status', 'Vehicle booking cancelled.');
    }
}
