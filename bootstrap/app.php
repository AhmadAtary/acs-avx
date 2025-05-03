<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'eng' => \App\Http\Middleware\engineerRole::class,
            'otp.verify' => \App\Http\Middleware\OtpVerifyMiddleware::class,
            'access.control' => \App\Http\Middleware\AccessControlMiddleware::class,
            'check.permission' => \App\Http\Middleware\CheckPermissions::class,
            // 'enduser.auth' => \App\Http\Middleware\EnsureEndUserIsAuthenticated::class,
            ]);

        $middleware->append(\App\Http\Middleware\CheckDatabaseConnection::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();

// ✅ Add this line below the return statement
require_once app_path('Helpers/helpers.php');
