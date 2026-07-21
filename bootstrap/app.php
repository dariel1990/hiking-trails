<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\VerifyAppKey;
use App\Support\DeveloperAlert;
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
        $schedule->command('gpx:clean-temp')->hourly()
            ->onFailure(fn () => DeveloperAlert::send('gpx:clean-temp', 'Command exited with a non-zero status.'));
        $schedule->command('subscriptions:expire-lapsed')->hourly()
            ->onFailure(fn () => DeveloperAlert::send('subscriptions:expire-lapsed', 'Command exited with a non-zero status.'));
        $schedule->command('subscriptions:send-expiry-reminders')->dailyAt('09:00')
            ->onFailure(fn () => DeveloperAlert::send('subscriptions:send-expiry-reminders', 'Command exited with a non-zero status.'));
        $schedule->command('subscriptions:send-trial-reminders')->dailyAt('09:15')
            ->onFailure(fn () => DeveloperAlert::send('subscriptions:send-trial-reminders', 'Command exited with a non-zero status.'));

        // Drains the database queue (notification emails) every minute via the
        // same schedule:run cron — no persistent worker process needed.
        $schedule->command('queue:work --stop-when-empty --tries=3 --max-time=55')
            ->everyMinute()
            ->withoutOverlapping();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
