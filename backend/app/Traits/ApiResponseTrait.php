<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponseTrait
{
    public function successResponse(
        array $data = [],
        string|null $message = null,
        int $code = Response::HTTP_OK
    ): JsonResponse {
        return $this->jsonResponse([
            'message' => $message ?? 'Operação realizada com sucesso.',
            'data' => $data,
        ], $code);
    }

    public function errorResponse(
        array $errors = [],
        string|null $message = null,
        int $code = Response::HTTP_BAD_REQUEST
    ): JsonResponse {
        return $this->jsonResponse([
            'message' => $message ?? 'Ocorreu um erro ao processar a requisição.',
            'errors' => $errors,
        ], $code);
    }

    protected function jsonResponse(array $body, int $code = Response::HTTP_OK): JsonResponse
    {
        return response()->json($body, $code);
    }

    protected function paginatedResponse(LengthAwarePaginator $paginator, string|null $resourceClass = null): array
    {
        $items = $paginator->getCollection();

        if ($resourceClass !== null && is_a($resourceClass, JsonResource::class, true)) {
            $items = $resourceClass::collection($items)->resolve();
        }

        return [
            'items' => $items,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'prev_page_url' => $paginator->previousPageUrl(),
            'next_page_url' => $paginator->nextPageUrl(),
        ];
    }
}
