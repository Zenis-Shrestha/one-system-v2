<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\IpWhitelistMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'ip.whitelist' => IpWhitelistMiddleware::class,
            'rate_limit_login' => \App\Http\Middleware\RateLimitLogin::class,
            'account_lockout' => \App\Http\Middleware\AccountLockoutMiddleware::class,
        ]);

        $middleware->group('api.protected', [
            IpWhitelistMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
