<?php

namespace App\Exceptions;

use App\Transformer\ApiResponseTransformer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Maps uncaught exceptions to the standard API error envelope
 * ({ success:false, message, errors }) for JSON/API requests.
 *
 * Registered globally via bootstrap/app.php -> withExceptions()->render().
 * Returns null for non-JSON requests, so Laravel's default handling (SPA shell,
 * guest redirects, etc.) stays intact.
 */
class ApiExceptionHandler
{
    public static function render(Throwable $e, Request $request): ?JsonResponse
    {
        if (!$request->expectsJson() && !$request->is('api/*')) {
            return null;
        }

        $empty = (object)[];

        if ($e instanceof ValidationException) {
            return ApiResponseTransformer::error($e->errors(), $e->getMessage(), 422);
        }

        if ($e instanceof AuthenticationException) {
            return ApiResponseTransformer::error($empty, 'Unauthenticated.', 401);
        }

        if ($e instanceof AuthorizationException) {
            return ApiResponseTransformer::error($empty, $e->getMessage() ?: 'This action is unauthorized.', 403);
        }

        if ($e instanceof ModelNotFoundException) {
            return ApiResponseTransformer::error($empty, 'Resource not found.', 404);
        }

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            $message = $e->getMessage() ?: self::defaultHttpMessage($status);
            return ApiResponseTransformer::error($empty, $message, $status);
        }

        // Unexpected error -> 500. Hide internals unless APP_DEBUG is on.
        if (config('app.debug')) {
            return ApiResponseTransformer::error($empty, $e->getMessage(), 500, [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        return ApiResponseTransformer::error($empty, 'Server Error.', 500);
    }

    private static function defaultHttpMessage(int $status): string
    {
        return match ($status) {
            403 => 'This action is unauthorized.',
            404 => 'Resource not found.',
            405 => 'Method not allowed.',
            429 => 'Too many requests.',
            default => 'Request failed.',
        };
    }
}
