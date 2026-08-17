<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        apiPrefix: ''
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        $middleware->api();
        $middleware->statefulApi();
        $middleware->validateCsrfTokens([
            'createCourse',
            'getAllCourses',
            'enroll',
            'setWatched',
            'signedIn',
            'course/*'
        ]);
        $middleware->trustProxies('*');

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    
    })->create();
