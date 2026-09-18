<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Semua request datang lewat Cloudflare → nginx; tanpa ini Laravel cuma
        // melihat IP edge/proxy. Catatan keamanan: X-Forwarded-For bisa menyisip
        // nilai spoof-an klien, jadi IP klien yang tepercaya dibaca dari header
        // CF-Connecting-IP (di-set edge Cloudflare) di AttendanceController.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'api.key'      => \App\Http\Middleware\ApiKey::class,
            'face.admin'   => \App\Http\Middleware\FaceAdmin::class,
            'admin.auth'   => \App\Http\Middleware\AdminAuth::class,
            'role'         => \App\Http\Middleware\RequireRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
