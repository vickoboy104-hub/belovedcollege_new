<?php

namespace Tests\Feature;

use Tests\TestCase;

class DirectoryReportWorkflowCorrectionsTest extends TestCase
{
    public function test_student_directory_keeps_only_essential_columns_and_pins_actions(): void
    {
        $css = file_get_contents(public_path('student-actions-overlay.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString(':nth-child(2)', $css);
        $this->assertStringContainsString(':nth-child(5)', $css);
        $this->assertStringContainsString(':nth-child(6)', $css);
        $this->assertStringContainsString('display: none !important', $css);
        $this->assertStringContainsString('position: sticky !important', $css);
        $this->assertStringContainsString('right: 0 !important', $css);
        $this->assertStringContainsString('overflow-x: clip !important', $css);
    }

    public function test_report_search_suggestions_are_optional_and_category_links_ignore_search_text(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));
        $script = file_get_contents(public_path('report-search-behavior.js'));

        $this->assertIsString($layout);
        $this->assertIsString($script);
        $this->assertStringContainsString('report-search-behavior.js', $layout);
        $this->assertStringContainsString("removeAttribute('list')", $script);
        $this->assertStringContainsString("removeAttribute('autofocus')", $script);
        $this->assertStringContainsString("removeAttribute('data-report-search-input')", $script);
        $this->assertStringContainsString('Browse student suggestions (optional)', $script);
        $this->assertStringContainsString("url.searchParams.delete('search')", $script);
    }

    public function test_modern_print_flow_correction_is_injected_after_the_base_stylesheet(): void
    {
        $middleware = file_get_contents(app_path('Http/Middleware/InjectReportPrintAssets.php'));
        $css = file_get_contents(public_path('report-print-modern-flow-fix.css'));

        $this->assertIsString($middleware);
        $this->assertIsString($css);
        $this->assertStringContainsString("['report-print-modern.css', 'report-print-modern-flow-fix.css']", $middleware);
        $this->assertStringContainsString('modern-report-scores', $css);
        $this->assertStringContainsString('flex: 0 0 auto !important', $css);
        $this->assertStringContainsString('height: auto !important', $css);
    }
}
