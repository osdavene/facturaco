<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:      __DIR__.'/../routes/web.php',
        api:      __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health:   '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->validateCsrfTokens(except: array_merge(
            ['webhooks/wompi'],
            env('APP_ENV') === 'testing' ? ['*'] : [],
        ));
        $middleware->alias([
            'empresa'           => \App\Http\Middleware\EnsureEmpresaSeleccionada::class,
            'backoffice'        => \App\Http\Middleware\EsBackoffice::class,
            'api.empresa'       => \App\Http\Middleware\SetEmpresaDesdeToken::class,
            'modulo'            => \App\Http\Middleware\ModuloActivo::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

    
