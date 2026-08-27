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

    public function test_student_dashboard_contains_only_personal_account_metric_categories(): void
    {
        $student = User::factory()->create([
            'role' => UserRole::Student,
        ]);

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('stats', function (array $stats): bool {
            return collect($stats)->pluck('label')->all() === [
                'Lesson Notes',
                'Assignments',
                'Published Reports',
                'Fees Owed',
            ];
        });
        $response->assertViewHas('financeSnapshot', null);
        $response->assertSee('Your personal academic account summary');
        $response->assertDontSee('Operational payment picture');
        $response->assertDontSee('Active Invoices');
        $response->assertDontSee('Payments Logged');
    }

    public function test_teacher_dashboard_contains_only_teacher_workload_metric_categories(): void
    {
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
        ]);

        $response = $this->actingAs($teacher)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('stats', function (array $stats): bool {
            return collect($stats)->pluck('label')->all() === [
                'Lessons Published',
                'Assignments Created',
                'Assessments Created',
                'Managed Classes',
            ];
        });
        $response->assertViewHas('financeSnapshot', null);
        $response->assertSee('Your teaching workload');
        $response->assertDontSee('Operational payment picture');
        $response->assertDontSee('Active Invoices');
        $response->assertDontSee('Payments Logged');
    }

    public function test_accountant_dashboard_is_finance_scoped_instead_of_people_scoped(): void
    {
        $accountant = User::factory()->create([
            'role' => UserRole::Accountant,
        ]);

        $response = $this->actingAs($accountant)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('stats', function (array $stats): bool {
            return collect($stats)->pluck('label')->all() === [
                'Active Invoices',
                'Payments Logged',
                'Outstanding Balance',
                'Collection Rate',
            ];
        });
        $response->assertSee('Finance activity and collection information');
    }
}
