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
        // Configure API middleware
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
        
        // Configure CORS
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);
        
        // Prevent search engines from indexing admin pages
        $middleware->append(\App\Http\Middleware\NoIndexAdminPages::class);
        
        // Configure rate limiting for API
        $middleware->throttleApi('60,1'); // 60 requests per minute

        // Custom middleware aliases
        $middleware->alias([
            'api.key' => \App\Http\Middleware\VerifyApiKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Force JSON error responses for API routes
        $exceptions->shouldRenderJsonWhen(function (\Illuminate\Http\Request $request) {
            return $request->is('api/*');
        });
    })->create();
