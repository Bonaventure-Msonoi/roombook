<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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

    public function test_admin_can_export_analytics_excel(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'department' => 'Operations Dept',
        ]);

        $from = now()->startOfMonth()->format('Y-m-d\TH:i');
        $to = now()->endOfDay()->format('Y-m-d\TH:i');

        $response = $this->actingAs($admin)
            ->get(route('admin.analytics.export', [
                'from' => $from,
                'to' => $to,
            ]));

        $response->assertOk();
        $response->assertHeader('content-disposition');
        $this->assertStringContainsString(
            'reservio-analytics-',
            $response->headers->get('content-disposition')
        );
        $this->assertStringContainsString('Workbook', $response->streamedContent());
        $this->assertStringContainsString('By Department', $response->streamedContent());
        $this->assertStringContainsString('Period start', $response->streamedContent());
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
            ->assertSee('Export Excel');
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
}
