<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // $middleware->trustProxies(at '*'); // laravel confia nos cabeçalhos de proxy 

        $middleware->append(App\Http\Middleware\SecurityHeaders::class);

        $middleware->alias([
            'account' => App\Http\Middleware\EnsureAccountType::class,
        ]);

        $middleware->redirectGuestsTo(fn (Request $request): string => match (true) {
            str_starts_with($request->path(), 'admin') => route('admin.login'),
            str_starts_with($request->path(), 'ubs/pacientes'),
            str_starts_with($request->path(), 'ubs/avaliacoes'),
            str_starts_with($request->path(), 'ubs/relatorios') => route('ubs.login'),
            default => route('ubs.login'),
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();