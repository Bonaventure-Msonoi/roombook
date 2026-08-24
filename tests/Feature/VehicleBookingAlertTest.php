<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use App\Notifications\VehicleBookingCancelledAlert;
use App\Notifications\VehicleBookingCreatedAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class VehicleBookingAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_users_receive_alert_when_vehicle_booking_is_created(): void
    {
        Notification::fake();

        $superUser = User::factory()->create([
            'role' => User::ROLE_SUPER_USER,
            'department' => 'Operations Dept',
        ]);

        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'department' => 'HR',
        ]);

        $vehicle = Vehicle::create([
            'number_plate' => 'KAE 500E',
            'engine_capacity' => '1800cc',
            'make' => 'Toyota Corolla',
            'category' => 'Saloon',
            'color' => '#1d4ed8',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('vehicles.bookings.store'), [
            'vehicle_id' => $vehicle->id,
            'title' => 'Branch visit',
            'destination' => 'CBD',
            'starts_at' => now()->addDays(1)->setTime(9, 0)->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDays(1)->setTime(11, 0)->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect(route('vehicles.dashboard'));

        Notification::assertSentTo($superUser, VehicleBookingCreatedAlert::class);
    }

    public function test_super_users_receive_alert_when_vehicle_booking_is_cancelled(): void
    {
        Notification::fake();

        $superUser = User::factory()->create([
            'role' => User::ROLE_SUPER_USER,
            'department' => 'Operations Dept',
        ]);

        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'department' => 'HR',
        ]);

        $vehicle = Vehicle::create([
            'number_plate' => 'KAF 600F',
            'engine_capacity' => '2000cc',
            'make' => 'Toyota Prado',
            'category' => 'SUV',
            'color' => '#0f766e',
            'is_active' => true,
        ]);

        $booking = VehicleBooking::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'title' => 'Field visit',
            'destination' => 'Industrial Area',
            'starts_at' => now()->addDays(1)->setTime(8, 0),
            'ends_at' => now()->addDays(1)->setTime(10, 0),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($user)->delete(route('vehicles.bookings.destroy', $booking));

        $response->assertRedirect();

        Notification::assertSentTo($superUser, VehicleBookingCancelledAlert::class);
    }
}
