<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class AnalyticsDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_analytics_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'department' => 'Operations Dept',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.analytics.index'))
            ->assertOk()
            ->assertSee('Analytics')
            ->assertSee('Bookings by department');
    }

    public function test_admin_can_export_vehicle_usage_with_trip_rows(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'department' => 'Operations Dept',
        ]);

        $staff = User::factory()->create([
            'name' => 'Jane Driver',
            'role' => User::ROLE_USER,
            'department' => 'Wealth Solutions',
        ]);

        $vehicle = Vehicle::create([
            'number_plate' => 'ABC 123',
            'engine_capacity' => '2000cc',
            'make' => 'Toyota Prado',
            'category' => 'SUV',
            'color' => '#6366f1',
            'is_active' => true,
        ]);

        VehicleBooking::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $staff->id,
            'title' => 'Client engagement',
            'destination' => 'Level field',
            'starts_at' => now()->addDays(2)->setTime(14, 0),
            'ends_at' => now()->addDays(2)->setTime(14, 45),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.analytics.export', [
                'from' => now()->startOfMonth()->format('Y-m-d\TH:i'),
                'to' => now()->endOfMonth()->format('Y-m-d\TH:i'),
            ]));

        $response->assertOk();
        $this->assertStringContainsString(
            'reservio-vehicle-usage-',
            $response->headers->get('content-disposition')
        );

        $xml = $this->firstSheetXml($response->streamedContent());

        $this->assertStringContainsString('Jane Driver', $xml);
        $this->assertStringContainsString('Wealth Solutions', $xml);
        $this->assertStringContainsString('Client engagement', $xml);
        $this->assertStringContainsString('Toyota Prado (ABC 123)', $xml);
        $this->assertStringContainsString('Level field', $xml);
    }

    public function test_admin_can_filter_analytics_by_date_range(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'department' => 'Finance Dept',
        ]);

        $from = now()->subDays(7)->format('Y-m-d\TH:i');
        $to = now()->format('Y-m-d\TH:i');

        $this->actingAs($admin)
            ->get(route('admin.analytics.index', [
                'from' => $from,
                'to' => $to,
            ]))
            ->assertOk()
            ->assertSee('Apply range')
            ->assertSee('Export vehicle usage');
    }

    public function test_export_rejects_invalid_range(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'department' => 'HR',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.analytics.export', [
                'from' => now()->format('Y-m-d\TH:i'),
                'to' => now()->subDay()->format('Y-m-d\TH:i'),
            ]))
            ->assertSessionHasErrors('to');
    }

    public function test_staff_cannot_view_analytics_dashboard(): void
    {
        $staff = User::factory()->create([
            'role' => User::ROLE_USER,
            'department' => 'HR',
        ]);

        $this->actingAs($staff)
            ->get(route('admin.analytics.index'))
            ->assertForbidden();
    }

    private function firstSheetXml(string $xlsx): string
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        $this->assertNotFalse($path);
        file_put_contents($path, $xlsx);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        @unlink($path);

        $this->assertIsString($xml);

        return $xml;
    }
}
