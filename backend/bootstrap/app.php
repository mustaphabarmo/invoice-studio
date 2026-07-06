<?php

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
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth_api' => \App\Http\Middleware\Authenticate::class,
        'member' => \App\Http\Middleware\EnsureMember::class,
        'admin' => \App\Http\Middleware\EnsureAdmin::class,
        ]);

       // API-first app: avoid redirecting unauthenticated requests to a non-existent `login` route.
       $middleware->redirectGuestsTo(fn () => null);
       $middleware->validateCsrfTokens(except: [

        '/api/v1/webhooks/xpouch',

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
