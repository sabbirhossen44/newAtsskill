<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function json(?string $message = null, mixed $data = null, int $statusCode = Response::HTTP_OK, array $errors = [], array $headers = [], array $extra = []): JsonResponse
    {
        $content = ['success' => $statusCode >= 200 && $statusCode < 300];

        if ($message) {
            $content['message'] = $message;
        }

        if ($data !== null && $data !== []) {
            $content['data'] = $this->resolveData($data);
        }

        if ($errors) {
            $content['errors'] = $errors;
        }

        if ($meta = $this->extractPaginationMeta($data)) {
            $content['meta'] = $meta;
        }

        foreach ($extra as $key => $value) {
            if (! array_key_exists($key, $content)) {
                $content[$key] = $value;
            }
        }

        return response()->json($content, $statusCode, $headers, JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_SLASHES);
    }

    protected function resolveData(mixed $data): mixed
    {
        if ($data instanceof ResourceCollection) {
            return $data->resolve(request());
        }

        return $data;
    }

    protected function extractPaginationMeta(mixed $data): ?array
    {
        $paginator = $data instanceof ResourceCollection ? $data->resource : $data;

        if (! $paginator instanceof Paginator) {
            return null;
        }

        $meta = [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
        ];

        if ($paginator instanceof LengthAwarePaginator) {
            $meta['total'] = $paginator->total();
            $meta['last_page'] = $paginator->lastPage();
        }

        return $meta;
    }
}
