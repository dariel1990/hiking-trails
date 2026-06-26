<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\VerifyAppKey;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'app.key' => VerifyAppKey::class,
        ]);

        $middleware->api(append: [
            VerifyAppKey::class,
        ]);

        // Stripe posts server-to-server; it can't carry a CSRF token (verified by signature).
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        // Add your scheduled tasks here
        $schedule->command('gpx:clean-temp')->hourly();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
