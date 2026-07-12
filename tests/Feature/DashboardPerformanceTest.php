<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_with_aggregated_metrics(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Students');
        $response->assertSee('Staff');
        $response->assertSee('Active Invoices');
        $response->assertSee('Payments Logged');
        $response->assertSee('Operational payment picture');
        $response->assertSee('dashboard-main-grid', false);
        $response->assertSee('interface-corrections.css', false);
        $response->assertViewHas('financeSnapshot', function (array $snapshot): bool {
            return $snapshot['students'] === 0
                && $snapshot['classes'] === 0
                && $snapshot['debtorStudents'] === 0
                && $snapshot['totalBilled'] === 0.0
                && $snapshot['totalCollected'] === 0.0
                && $snapshot['outstanding'] === 0.0;
        });
    }
}
