<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->group(base_path('routes/guru.php'));
            Route::middleware('web')->group(base_path('routes/admin.php'));
            Route::middleware('web')->group(base_path('routes/siswa.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Kecualikan login dan logout dari validasi CSRF agar tidak pernah error 419
        $middleware->validateCsrfTokens(except: [
            'login',
            'logout',
            'siswa/login',
            'siswa/logout',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Jika terjadi error 419 (Page Expired / Token Mismatch), otomatis arahkan kembali ke login dengan pesan ramah
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() === 419) {
                return redirect('/login')->with('error', 'Sesi Anda telah kedaluwarsa. Silakan masuk kembali.');
            }
        });
    })->create();
