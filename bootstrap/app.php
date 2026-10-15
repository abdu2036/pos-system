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
        'admin' => 'App\\Http\\Middleware\\AdminMiddleware',
        'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
    ]);
})
//وضعنا هنا الميدل وير الخاص بالتحقق من وجود وردية مفتوحة للمستخدم قبل الدخول إلى نقطة البيع
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'active.shift' => \App\Http\Middleware\CheckActiveShift::class,
    ]);
})
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

    
