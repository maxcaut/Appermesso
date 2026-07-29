<?php

use App\Http\Middleware\RequireSupabaseAuth;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Render termina HTTPS sul proprio reverse proxy e inoltra la richiesta
        // al container via HTTP. Fidarsi del proxy preserva lo schema originale.
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'supabase.auth' => RequireSupabaseAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
