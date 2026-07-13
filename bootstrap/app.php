<?php

use App\Http\Middleware\DeferAutoplayMedia;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\InjectReportPrintAssets;
use App\Http\Middleware\OptimizeUploadedMedia;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Codespaces and most modern hosting platforms place Laravel behind a
        // reverse proxy. Trust the forwarded host/protocol headers so route(),
        // asset(), and Vite URLs use the public HTTPS address instead of
        // http://localhost:8000.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_PREFIX,
        );

        $middleware->web(append: [
            OptimizeUploadedMedia::class,
            DeferAutoplayMedia::class,
            InjectReportPrintAssets::class,
        ]);

        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
