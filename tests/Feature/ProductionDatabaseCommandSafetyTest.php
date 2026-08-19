<?php

namespace Tests\Feature;

use App\Providers\ProductionSafetyServiceProvider;
use RuntimeException;
use Tests\TestCase;

class ProductionDatabaseCommandSafetyTest extends TestCase
{
    public function test_destructive_demo_database_command_is_blocked_in_production(): void
    {
        $originalEnvironment = app()->environment();
        app()->detectEnvironment(fn (): string => 'production');

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('blocked in production');
            ProductionSafetyServiceProvider::assertCommandAllowed('db:seed');
        } finally {
            app()->detectEnvironment(fn (): string => $originalEnvironment);
        }
    }

    public function test_normal_forward_migration_command_remains_allowed_in_production(): void
    {
        $originalEnvironment = app()->environment();
        app()->detectEnvironment(fn (): string => 'production');

        try {
            ProductionSafetyServiceProvider::assertCommandAllowed('migrate');
            $this->addToAssertionCount(1);
        } finally {
            app()->detectEnvironment(fn (): string => $originalEnvironment);
        }
    }
}
