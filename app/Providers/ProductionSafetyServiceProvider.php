<?php

namespace App\Providers;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class ProductionSafetyServiceProvider extends ServiceProvider
{
    /** @var array<int, string> */
    protected const BLOCKED_PRODUCTION_COMMANDS = [
        'db:seed',
        'db:wipe',
        'migrate:fresh',
        'migrate:refresh',
        'migrate:reset',
        'migrate:rollback',
    ];

    public function boot(): void
    {
        Event::listen(CommandStarting::class, function (CommandStarting $event): void {
            static::assertCommandAllowed($event->command);
        });
    }

    public static function assertCommandAllowed(string $command): void
    {
        if (! app()->environment('production')) {
            return;
        }

        if (! in_array($command, self::BLOCKED_PRODUCTION_COMMANDS, true)) {
            return;
        }

        throw new RuntimeException(
            "The [{$command}] command is blocked in production to protect live school data. "
            .'Use normal forward migrations for production and run demo seeding only in a disposable non-production environment.'
        );
    }
}
