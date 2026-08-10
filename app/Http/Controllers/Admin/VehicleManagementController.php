<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehicleRequest;
use App\Models\Vehicle;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VehicleManagementController extends Controller
{
    public function index(): View
    {
        $vehicles = Vehicle::orderBy('make')->get();

        return view('admin.vehicles.index', compact('vehicles'));
    }

    public function create(): View
    {
        return view('admin.vehicles.form', [
            'vehicle' => new Vehicle(['color' => '#6366f1', 'is_active' => true]),
            'categories' => Vehicle::CATEGORIES,
        ]);
    }

    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        $vehicle = Vehicle::create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogger::log(
            'vehicle.created',
            "Vehicle added: {$vehicle->displayName()}",
            $vehicle,
            ['number_plate' => $vehicle->number_plate, 'category' => $vehicle->category]
        );

        return redirect()
            ->route('admin.vehicles.index')
            ->with('status', 'Vehicle added successfully.');
    }

    public function edit(Vehicle $vehicle): View
    {
        return view('admin.vehicles.form', [
            'vehicle' => $vehicle,
            'categories' => Vehicle::CATEGORIES,
        ]);
    }

    public function update(StoreVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $vehicle->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogger::log(
            'vehicle.updated',
            "Vehicle updated: {$vehicle->displayName()}",
            $vehicle,
            ['number_plate' => $vehicle->number_plate, 'is_active' => $vehicle->is_active]
        );

        return redirect()
            ->route('admin.vehicles.index')
            ->with('status', 'Vehicle updated successfully.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $snapshot = [
            'number_plate' => $vehicle->number_plate,
            'make' => $vehicle->make,
            'display_name' => $vehicle->displayName(),
        ];

        $vehicle->delete();

        AuditLogger::log(
            'vehicle.deleted',
            "Vehicle removed: {$snapshot['display_name']}",
            null,
            $snapshot
        );

        return redirect()
            ->route('admin.vehicles.index')
            ->with('status', 'Vehicle removed.');
    }
}
