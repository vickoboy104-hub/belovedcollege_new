<?php

namespace Tests\Feature;

use Tests\TestCase;

class ReportSearchLayoutTest extends TestCase
{
    public function test_report_directory_search_control_has_a_compact_fixed_height(): void
    {
        $css = file_get_contents(public_path('report-search-controls.css'));
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));
        $reportIndex = file_get_contents(resource_path('views/admin/reports/index.blade.php'));

        $this->assertIsString($css);
        $this->assertIsString($layout);
        $this->assertIsString($reportIndex);
        $this->assertStringContainsString('route-admin-reports-index #report-student-search-form', $css);
        $this->assertStringContainsString('height: 2.75rem !important', $css);
        $this->assertStringContainsString('max-height: 2.75rem !important', $css);
        $this->assertStringContainsString('report-search-controls.css', $layout);
        $this->assertStringContainsString('id="report-student-search-form"', $reportIndex);
    }
}
