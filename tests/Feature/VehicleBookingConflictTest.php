<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleBookingConflictTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_book_two_vehicles_for_overlapping_time_slots(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'department' => 'HR',
        ]);

        $firstVehicle = Vehicle::create([
            'number_plate' => 'KAA 100A',
            'engine_capacity' => '1800cc',
            'make' => 'Toyota Corolla',
            'category' => 'Saloon',
            'color' => '#111827',
            'is_active' => true,
        ]);

        $secondVehicle = Vehicle::create([
            'number_plate' => 'KBB 200B',
            'engine_capacity' => '2000cc',
            'make' => 'Nissan X-Trail',
            'category' => 'SUV',
            'color' => '#2563eb',
            'is_active' => true,
        ]);

        VehicleBooking::create([
            'vehicle_id' => $firstVehicle->id,
            'user_id' => $user->id,
            'title' => 'Morning trip',
            'destination' => 'Town office',
            'starts_at' => now()->addDays(1)->setTime(9, 0),
            'ends_at' => now()->addDays(1)->setTime(11, 0),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($user)->post(route('vehicles.bookings.store'), [
            'vehicle_id' => $secondVehicle->id,
            'title' => 'Client visit',
            'destination' => 'Westlands',
            'starts_at' => now()->addDays(1)->setTime(10, 0)->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDays(1)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);

        $response
            ->assertSessionHasErrors([
                'starts_at' => 'You already have a vehicle booked for this time slot.',
            ]);

        $this->assertDatabaseCount('vehicle_bookings', 1);
    }

    public function test_user_can_book_back_to_back_vehicle_slots(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'department' => 'HR',
        ]);

        $firstVehicle = Vehicle::create([
            'number_plate' => 'KCC 300C',
            'engine_capacity' => '1800cc',
            'make' => 'Mazda Demio',
            'category' => 'Saloon',
            'color' => '#0f766e',
            'is_active' => true,
        ]);

        $secondVehicle = Vehicle::create([
            'number_plate' => 'KDD 400D',
            'engine_capacity' => '2200cc',
            'make' => 'Toyota Hilux',
            'category' => 'Pickup',
            'color' => '#7c3aed',
            'is_active' => true,
        ]);

        VehicleBooking::create([
            'vehicle_id' => $firstVehicle->id,
            'user_id' => $user->id,
            'title' => 'Morning trip',
            'destination' => 'Town office',
            'starts_at' => now()->addDays(1)->setTime(9, 0),
            'ends_at' => now()->addDays(1)->setTime(11, 0),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($user)->post(route('vehicles.bookings.store'), [
            'vehicle_id' => $secondVehicle->id,
            'title' => 'Afternoon trip',
            'destination' => 'Airport',
            'starts_at' => now()->addDays(1)->setTime(11, 0)->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDays(1)->setTime(12, 30)->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect(route('vehicles.dashboard'));

        $this->assertDatabaseCount('vehicle_bookings', 2);
    }
}
