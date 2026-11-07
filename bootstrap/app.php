<?php

use App\Http\Middleware\EnsureDemoPartner;
use App\Http\Middleware\EnsurePartnerApproved;
use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
            'partner.approved' => EnsurePartnerApproved::class,
            'partner.demo' => EnsureDemoPartner::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('account.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Default exception handling termasuk HTTP 403, 404, dan validasi.
    })->create();
