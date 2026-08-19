<?php

namespace Tests\Feature;

use Tests\TestCase;

class DeploymentSafetyTest extends TestCase
{
    public function test_shared_host_root_htaccess_blocks_sensitive_laravel_paths(): void
    {
        $contents = file_get_contents(base_path('.htaccess'));

        $this->assertIsString($contents);
        $this->assertStringContainsString('Options -Indexes', $contents);
        $this->assertStringContainsString('app|bootstrap|config|database|resources|routes|storage|tests|vendor|node_modules', $contents);
        $this->assertStringContainsString('\\.env', $contents);
        $this->assertStringContainsString('RewriteRule ^(.*)$ public/$1 [L]', $contents);
    }

    public function test_production_ftp_workflow_is_manual_and_dry_run_first(): void
    {
        $contents = file_get_contents(base_path('.github/workflows/manual-production-deploy.yml'));

        $this->assertIsString($contents);
        $this->assertStringContainsString('workflow_dispatch:', $contents);
        $this->assertStringContainsString('default: true', $contents);
        $this->assertStringContainsString('environment: production', $contents);
        $this->assertStringContainsString('dry-run: ${{ inputs.dry_run }}', $contents);
        $this->assertStringContainsString('dangerous-clean-slate: false', $contents);
        $this->assertStringContainsString(".env\n", $contents);
        $this->assertStringNotContainsString("push:\n", $contents);
    }
}
