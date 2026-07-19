<?php

namespace Tests\Feature;

use Tests\TestCase;

class GlobalTableConsistencyAndPortraitReportTest extends TestCase
{
    public function test_portal_layout_loads_the_final_global_table_assets(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

        $this->assertIsString($layout);
        $this->assertStringContainsString('table-consistency.css', $layout);
        $this->assertStringContainsString('table-consistency.js', $layout);
        $this->assertStringContainsString('20260720-global-table-consistency-1', $layout);
    }

    public function test_global_table_controller_covers_actions_and_entity_directories(): void
    {
        $script = file_get_contents(public_path('table-consistency.js'));
        $stylesheet = file_get_contents(public_path('table-consistency.css'));

        $this->assertIsString($script);
        $this->assertIsString($stylesheet);

        $this->assertStringContainsString("route-admin-students-index", $script);
        $this->assertStringContainsString("route-admin-parents-index", $script);
        $this->assertStringContainsString("route-admin-staff-index", $script);
        $this->assertStringContainsString("route-admin-reports-index", $script);
        $this->assertStringContainsString("table-action-column", $script);
        $this->assertStringContainsString("table-secondary-column", $script);
        $this->assertStringContainsString("has-multiple-row-actions", $script);

        $this->assertStringContainsString('.admin-data-table .table-action-column', $stylesheet);
        $this->assertStringContainsString('position: sticky !important', $stylesheet);
        $this->assertStringContainsString('right: 0 !important', $stylesheet);
        $this->assertStringContainsString('.admin-data-table.is-entity-directory-table .table-secondary-column', $stylesheet);
        $this->assertStringContainsString('display: none !important', $stylesheet);
    }

    public function test_modern_report_print_uses_vertical_portrait_flow_without_stretched_sections(): void
    {
        $stylesheet = file_get_contents(public_path('report-print-modern-flow-fix.css'));
        $middleware = file_get_contents(app_path('Http/Middleware/InjectReportPrintAssets.php'));

        $this->assertIsString($stylesheet);
        $this->assertIsString($middleware);

        $this->assertStringContainsString('True portrait document flow', $stylesheet);
        $this->assertStringContainsString('body.report-print-modern .modern-report-development', $stylesheet);
        $this->assertStringContainsString('body.report-print-modern .modern-report-remarks', $stylesheet);
        $this->assertStringContainsString('body.report-print-modern .modern-report-footer', $stylesheet);
        $this->assertStringContainsString('grid-template-columns: none !important', $stylesheet);
        $this->assertStringContainsString('height: auto !important', $stylesheet);
        $this->assertStringNotContainsString('flex: 1 1 auto', $stylesheet);
        $this->assertStringNotContainsString('grid-template-columns: repeat(2, minmax(0, 1fr))', $stylesheet);

        $this->assertStringContainsString('report-print-modern-flow-fix.css', $middleware);
        $this->assertStringContainsString('20260720-report-print-portrait-flow-2', $middleware);
    }
}
