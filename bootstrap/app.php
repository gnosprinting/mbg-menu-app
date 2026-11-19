<?php

// Workaround untuk hosting yang masih memakai classmap Composer lama.
// Setelah `composer dump-autoload -o` berhasil, blok ini boleh dihapus.
$loader = require __DIR__.'/../vendor/autoload.php';

if ($loader instanceof \Composer\Autoload\ClassLoader) {
    $loader->setClassMapAuthoritative(false);
    $loader->addPsr4('App\\', __DIR__.'/../app', true);
    $loader->addPsr4('Database\\Seeders\\', __DIR__.'/../database/seeders', true);
}

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
        $middleware->alias([
            'peran' => \App\Http\Middleware\PeranMiddleware::class,
        ]);
        $middleware->appendToGroup('web', \App\Http\Middleware\AuditLog::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
