<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\RedirectResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Illuminate\Http\Request $request): null|RedirectResponse {
            if (! $request->expectsJson() && method_exists($e, 'getStatusCode') && $e->getStatusCode() === 401) {
                // Редиректим на авторизацию (401-ое исключение)
                return redirect('/auth/keycloak/redirect');
            }
        });
    })
    ->create();
