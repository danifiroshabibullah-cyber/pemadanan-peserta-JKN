<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Alias middleware untuk membatasi akses berdasarkan role
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) =>
                $request->is('api/*') || $request->expectsJson(),
        );

    })
    ->create();

// Dukungan serverless Vercel / AWS Lambda dengan filesystem read-only
$storageDir = getenv('APP_STORAGE') ?: ($_ENV['APP_STORAGE'] ?? null);
if ($storageDir) {
    $app->useStoragePath($storageDir);
    foreach ([
        '/framework/views',
        '/framework/cache/data',
        '/framework/sessions',
        '/logs',
        '/app',
    ] as $sub) {
        $full = $storageDir . $sub;
        if (!is_dir($full)) {
            @mkdir($full, 0777, true);
        }
    }
}

return $app;
