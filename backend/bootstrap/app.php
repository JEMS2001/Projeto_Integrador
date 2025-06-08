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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'checkscopes' => \App\Http\Middleware\CheckScopes::class,
            'checkforanyScope' => \App\Http\Middleware\CheckForAnyScope::class,
            'oauth2session' => \App\Http\Middleware\ManageOAuth2Session::class,
            'sessionmgmt' => \App\Http\Middleware\SessionManagement::class,
            'passportcookies' => \App\Http\Middleware\PassportCookieManagement::class,
        ]);
        
        // Add OAuth2 session middleware to API routes - temporarily disabled for setup
        // $middleware->api(append: [
        //     \App\Http\Middleware\ManageOAuth2Session::class,
        //     \App\Http\Middleware\SessionManagement::class,
        //     \App\Http\Middleware\PassportCookieManagement::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
