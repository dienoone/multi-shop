<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponse
{
    protected function success(mixed $data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => null,
            'error' => null
        ], $status);
    }

    protected function paginated(ResourceCollection $resource, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $resource->resolve(),
            'meta' => self::buildMeta($resource->resource),
            'error' => null,
        ], $status);
    }

    protected function created(mixed $data = null, string $message = 'Created successfully'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    protected function noContent(string $message = 'Deleted successfully'): JsonResponse
    {
        return $this->success(null, $message, 200);
    }

    // TODO add debug if the env in development stage
    protected function error(string $message = 'Something went wrong', string $code = 'ERROR', int $status = 500, array $errors = [], ?string $errorId = null): JsonResponse
    {
        $error = [
            'code' => $code,
            'message' => $message,
        ];

        if ($errorId !== null) {
            $error['error_id'] = $errorId;
        }

        if (!empty($errors)) {
            $error['errors'] = $errors;
        }

        $error['timestamp'] = now()->toISOString();

        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'meta' => null,
            'error' => $error,
        ], $status);
    }

    private static function buildMeta(AbstractPaginator|AbstractCursorPaginator $paginator): array
    {
        // Cursor paginator — no total/page numbers
        if ($paginator instanceof AbstractCursorPaginator) {
            return [
                'type'          => 'cursor',
                'per_page'      => $paginator->perPage(),
                'next_cursor'   => $paginator->nextCursor()?->encode(),
                'prev_cursor'   => $paginator->previousCursor()?->encode(),
                'next_page_url' => $paginator->nextPageUrl(),
                'prev_page_url' => $paginator->previousPageUrl(),
                'has_more'      => $paginator->hasMorePages(),
            ];
        }

        // paginate()
        if (method_exists($paginator, 'total')) {
            return [
                'type'          => 'length_aware',
                'total'         => $paginator->total(),
                'per_page'      => $paginator->perPage(),
                'current_page'  => $paginator->currentPage(),
                'last_page'     => $paginator->lastPage(),
                'from'          => $paginator->firstItem(),
                'to'            => $paginator->lastItem(),
                'next_page_url' => $paginator->nextPageUrl(),
                'prev_page_url' => $paginator->previousPageUrl(),
                'has_more'      => $paginator->hasMorePages(),
            ];
        }

        // simplePaginate()
        return [
            'type'          => 'simple',
            'per_page'      => $paginator->perPage(),
            'current_page'  => $paginator->currentPage(),
            'from'          => $paginator->firstItem(),
            'to'            => $paginator->lastItem(),
            'next_page_url' => $paginator->nextPageUrl(),
            'prev_page_url' => $paginator->previousPageUrl(),
            'has_more'      => $paginator->hasMorePages(),
        ];
    }
}
