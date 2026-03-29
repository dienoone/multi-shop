<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionHandler
{
    use ApiResponse;

    public static function handle(Throwable $e): JsonResponse
    {
        $errorId = (string) Str::uuid();

        self::log($e, $errorId);

        return match (true) {
            $e instanceof ValidationException            => self::handleValidation($e, $errorId),
            $e instanceof ModelNotFoundException         => self::handleModelNotFound($e, $errorId),
            $e instanceof NotFoundHttpException          => self::handleNotFound($e, $errorId),
            $e instanceof AuthenticationException        => self::handleUnauthenticated($e, $errorId),
            $e instanceof AccessDeniedHttpException      => self::handleUnauthorized($e, $errorId),
            $e instanceof AuthorizationException         => self::handleUnauthorized($e, $errorId),
            $e instanceof MethodNotAllowedHttpException  => self::handleMethodNotAllowed($e, $errorId),
            $e instanceof QueryException                 => self::handleQuery($e, $errorId),
            $e instanceof HttpException                  => self::handleHttp($e, $errorId),
            default                                      => self::handleGeneric($e, $errorId),
        };
    }

    // Logging

    private static function log(Throwable $e, string $errorId): bool
    {
        $silent = [
            ValidationException::class,
            AuthenticationException::class,
            AuthorizationException::class,
            AccessDeniedHttpException::class,
            NotFoundHttpException::class,
        ];

        if (in_array(get_class($e), $silent)) {
            return false;
        }

        Log::error('API Error', [
            'error_id'  => $errorId,
            'message'   => $e->getMessage(),
            'exception' => get_class($e),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'trace'     => $e->getTraceAsString(),
            'url'       => request()->fullUrl(),
            'method'    => request()->method(),
            'ip'        => request()->ip(),
            'user_id'   => Auth::id() ?? 'guest',
        ]);

        return true;
    }

    // Handlers

    private static function handleValidation(ValidationException $e, string $errorId): JsonResponse
    {
        return (new self)->error(
            message: 'Validation failed',
            code: 'VALIDATION_ERROR',
            status: 422,
            errors: $e->errors(),
            errorId: $errorId
        );
    }

    private static function handleModelNotFound(ModelNotFoundException $e, string $errorId): JsonResponse
    {
        $model = class_basename($e->getModel());

        return (new self)->error(
            message: "{$model} not found",
            code: 'RESOURCE_NOT_FOUND',
            status: 404,
            errorId: $errorId
        );
    }

    private static function handleNotFound(NotFoundHttpException $e, string $errorId): JsonResponse
    {
        return (new self)->error(
            message: 'Route not found',
            code: 'ROUTE_NOT_FOUND',
            status: 404,
            errorId: $errorId
        );
    }

    private static function handleUnauthenticated(AuthenticationException $e, string $errorId): JsonResponse
    {
        return (new self)->error(
            message: 'Unauthenticated',
            code: 'UNAUTHENTICATED',
            status: 401,
            errorId: $errorId
        );
    }

    private static function handleUnauthorized(AuthorizationException|AccessDeniedHttpException $e, string $errorId): JsonResponse
    {
        return (new self)->error(
            message: 'You do not have permission to perform this action',
            code: 'UNAUTHORIZED',
            status: 403,
            errorId: $errorId
        );
    }

    private static function handleMethodNotAllowed(MethodNotAllowedHttpException $e, string $errorId): JsonResponse
    {
        return (new self)->error(
            message: 'Method not allowed',
            code: 'METHOD_NOT_ALLOWED',
            status: 405,
            errorId: $errorId
        );
    }

    private static function handleQuery(QueryException $e, string $errorId): JsonResponse
    {
        return (new self)->error(
            message: app()->isProduction() ? 'A database error occurred' : $e->getMessage(),
            code: 'DATABASE_ERROR',
            status: 500,
            errorId: $errorId
        );
    }

    private static function handleHttp(HttpException $e, string $errorId): JsonResponse
    {
        return (new self)->error(
            message: $e->getMessage() ?: 'HTTP error occurred',
            code: 'HTTP_ERROR',
            status: $e->getStatusCode(),
            errorId: $errorId
        );
    }

    private static function handleGeneric(Throwable $e, string $errorId): JsonResponse
    {
        return (new self)->error(
            message: app()->isProduction() ? 'An unexpected error occurred' : $e->getMessage(),
            code: 'SERVER_ERROR',
            status: 500,
            errorId: $errorId
        );
    }
}
