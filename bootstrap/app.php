<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        // Run monthly batch forecasting on the 1st at 2:00 AM
        $schedule->command('app:forecast-all', ['--model' => 'lstm'])
            ->monthlyOn(1, '02:00')
            ->timezone('Asia/Jakarta')
            ->onFailure(function () {
                \Log::error('Scheduled forecast batch failed');
            })
            ->onSuccess(function () {
                \Log::info('Scheduled forecast batch completed successfully');
            });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
