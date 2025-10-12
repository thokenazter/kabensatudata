<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            // Alias lain yang sudah ada...
            'save-url' => \App\Http\Middleware\SavePreviousUrl::class,
            'panel.auth' => \App\Http\Middleware\PanelAuth::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (PostTooLargeException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Ukuran data yang diunggah melebihi batas server. Silakan perkecil file atau hubungi admin.',
                ], 413);
            }
            return redirect()->back()
                ->withInput()
                ->withErrors(['file' => 'Ukuran data yang diunggah melebihi batas server. Silakan perkecil file atau hubungi admin.']);
        });
    })->create();
