<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/routes.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
        foreach (glob(base_path('routes/*.routes.php')) as $file) {
            Route::middleware('api')
                ->group($file);
        }
    },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
