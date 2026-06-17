<?php

use App\Exceptions\ApiExceptionHandler;
use App\Http\Middleware\SetCompanyDatabaseConnection;
use App\Http\Middleware\UserHasPermission;
use App\Http\Middleware\UserHasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(headers: Request::HEADER_X_FORWARDED_FOR
            | Request::HEADER_X_FORWARDED_HOST
            | Request::HEADER_X_FORWARDED_PORT
            | Request::HEADER_X_FORWARDED_PROTO
            | Request::HEADER_X_FORWARDED_AWS_ELB);

        $middleware->trimStrings(except: [
            'current_password',
            'password',
            'password_confirmation',
        ]);

        $middleware->redirectGuestsTo('/login');

        $middleware->api(prepend: [
            'throttle:api',
        ]);

        $middleware->alias([
            'company' => SetCompanyDatabaseConnection::class,
            'role' => UserHasRole::class,
            'permission' => UserHasPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontFlash([
            'current_password',
            'password',
            'password_confirmation',
        ]);

        $exceptions->render(fn(Throwable $e, Request $request) => ApiExceptionHandler::render($e, $request));
    })
    ->create();
