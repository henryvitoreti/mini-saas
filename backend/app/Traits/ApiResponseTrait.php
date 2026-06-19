<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
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
}
