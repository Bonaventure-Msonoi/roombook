<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleBookingRequest;
use App\Http\Requests\UpdateVehicleBookingRequest;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use App\Services\AuditLogger;
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
        return view('vehicles.bookings.form', [
            'booking' => new VehicleBooking(),
            'vehicles' => Vehicle::active()->orderBy('make')->get(),
            'users' => $this->selectableUsers($request->user()),
            'canBookOnBehalf' => $request->user()->canBookOnBehalf(),
            'canReassign' => false,
            'selectedVehicleId' => $request->integer('vehicle'),
            'selectedDate' => $request->input('date'),
            'selectedStartsAt' => $request->input('starts_at'),
            'selectedEndsAt' => $request->input('ends_at'),
        ]);
    }

    public function store(StoreVehicleBookingRequest $request): RedirectResponse
    {
        $ownerId = $request->bookingOwnerId();

        $booking = VehicleBooking::create([
            'vehicle_id' => $request->integer('vehicle_id'),
            'user_id' => $ownerId,
            'title' => $request->input('title'),
            'starts_at' => $request->input('starts_at'),
            'ends_at' => $request->input('ends_at'),
            'status' => 'confirmed',
        ]);

        $booking->load(['vehicle', 'user']);
        $onBehalf = $ownerId !== $request->user()->id;

        AuditLogger::log(
            $onBehalf ? 'vehicle_booking.created_on_behalf' : 'vehicle_booking.created',
            $onBehalf
                ? "Vehicle booking created on behalf of {$booking->user->name}: {$booking->title}"
                : "Vehicle booking created: {$booking->title}",
            $booking,
            [
                'vehicle' => $booking->vehicle?->displayName(),
                'owner' => $booking->user?->email,
                'starts_at' => (string) $booking->starts_at,
                'ends_at' => (string) $booking->ends_at,
                'reason' => $request->input('reason'),
            ]
        );

        return redirect()
            ->route('vehicles.dashboard')
            ->with('status', $onBehalf ? 'Vehicle booked on behalf of '.$booking->user->name.'.' : 'Vehicle booked successfully.');
    }

    public function edit(VehicleBooking $vehicleBooking): View
    {
        $user = auth()->user();

        if ($vehicleBooking->user_id !== $user->id && ! $user->canManageAnyBooking()) {
            abort(403);
        }

        if ($vehicleBooking->status !== 'confirmed' || $vehicleBooking->ends_at->isPast()) {
            return redirect()
                ->route('vehicles.bookings.admin')
                ->with('error', 'Only upcoming confirmed bookings can be edited.');
        }

        $vehicles = Vehicle::active()->orderBy('make')->get();
        if ($vehicleBooking->vehicle && ! $vehicles->contains('id', $vehicleBooking->vehicle_id)) {
            $vehicles->prepend($vehicleBooking->vehicle);
        }

        return view('vehicles.bookings.form', [
            'booking' => $vehicleBooking,
            'vehicles' => $vehicles,
            'users' => $this->selectableUsers($user),
            'canBookOnBehalf' => false,
            'canReassign' => $user->canManageAnyBooking(),
            'selectedVehicleId' => $vehicleBooking->vehicle_id,
            'selectedDate' => null,
            'selectedStartsAt' => $vehicleBooking->starts_at->format('Y-m-d\TH:i'),
            'selectedEndsAt' => $vehicleBooking->ends_at->format('Y-m-d\TH:i'),
        ]);
    }

    public function update(UpdateVehicleBookingRequest $request, VehicleBooking $vehicleBooking): RedirectResponse
    {
        $before = [
            'vehicle_id' => $vehicleBooking->vehicle_id,
            'user_id' => $vehicleBooking->user_id,
            'title' => $vehicleBooking->title,
            'starts_at' => (string) $vehicleBooking->starts_at,
            'ends_at' => (string) $vehicleBooking->ends_at,
        ];

        $data = [
            'vehicle_id' => $request->integer('vehicle_id'),
            'title' => $request->input('title'),
            'starts_at' => $request->input('starts_at'),
            'ends_at' => $request->input('ends_at'),
        ];

        if ($request->user()->canManageAnyBooking()) {
            $data['user_id'] = $request->integer('user_id');
        }

        $vehicleBooking->update($data);
        $vehicleBooking->load(['vehicle', 'user']);

        AuditLogger::log(
            'vehicle_booking.updated',
            "Vehicle booking updated: {$vehicleBooking->title}",
            $vehicleBooking,
            [
                'before' => $before,
                'after' => [
                    'vehicle_id' => $vehicleBooking->vehicle_id,
                    'user_id' => $vehicleBooking->user_id,
                    'title' => $vehicleBooking->title,
                    'starts_at' => (string) $vehicleBooking->starts_at,
                    'ends_at' => (string) $vehicleBooking->ends_at,
                    'vehicle' => $vehicleBooking->vehicle?->displayName(),
                    'owner' => $vehicleBooking->user?->email,
                ],
                'reason' => $request->input('reason'),
            ]
        );

        $redirect = $request->user()->canAccessAdminDashboard()
            ? route('vehicles.bookings.admin')
            : route('vehicles.bookings.mine');

        return redirect($redirect)->with('status', 'Vehicle booking updated successfully.');
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

    public function destroy(Request $request, VehicleBooking $vehicleBooking): RedirectResponse
    {
        $user = auth()->user();

        if ($vehicleBooking->user_id !== $user->id && ! $user->canCancelAnyBooking()) {
            abort(403);
        }

        $vehicleBooking->load(['vehicle', 'user']);
        $vehicleBooking->update(['status' => 'cancelled']);

        AuditLogger::log(
            'vehicle_booking.cancelled',
            "Vehicle booking cancelled: {$vehicleBooking->title}",
            $vehicleBooking,
            [
                'vehicle' => $vehicleBooking->vehicle?->displayName(),
                'owner_id' => $vehicleBooking->user_id,
                'owner' => $vehicleBooking->user?->email,
                'reason' => $request->input('reason'),
            ]
        );

        return back()->with('status', 'Vehicle booking cancelled.');
    }

    private function selectableUsers(User $actor)
    {
        if (! $actor->canBookOnBehalf() && ! $actor->canManageAnyBooking()) {
            return collect();
        }

        return User::active()->orderBy('name')->get(['id', 'name', 'email', 'role']);
    }
}
