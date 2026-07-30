<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends ApiBaseController
{
    public function __construct(private readonly RoleService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->useSimpleResourceForList($request);

        $items = $this->service->index($request);
        $data = $this->paginatedResponse($items, RoleResource::class);

        return $this->successResponse(data: $data);
    }

    public function search(Request $request): JsonResponse
    {
        $this->useSimpleResourceForList($request);

        $items = $this->service->search($request);
        $data = RoleResource::collection($items)->resolve();

        return $this->successResponse(data: $data);
    }

    public function options(Request $request): JsonResponse
    {
        $options = $this->service->options($request);
        return $this->successResponse(data: compact('options'));
    }

    public function show(int $id): JsonResponse
    {
        $item = $this->service->show($id);

        if ($item === null) {
            return $this->errorResponse(message: 'Perfil não encontrado.', code: Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse(data: RoleResource::make($item)->resolve());
    }

    public function store(RoleRequest $request): JsonResponse
    {
        try {
            $item = $this->service->store($request->validated());

            return $this->successResponse(
                data: RoleResource::make($item)->resolve(),
                message: 'Perfil cadastrado com sucesso.',
                code: Response::HTTP_CREATED,
            );
        } catch (Exception $exception) {
            Log::error($exception);
            return $this->errorResponse(message: 'Erro ao criar perfil.', code: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(RoleRequest $request, int $id): JsonResponse
    {
        try {
            $item = $this->service->update($id, $request->validated());

            if ($item === null) {
                return $this->errorResponse(message: 'Perfil não encontrado.', code: Response::HTTP_NOT_FOUND);
            }

            if ($item === false) {
                return $this->errorResponse(
                    message: 'Este perfil é protegido e não pode ser editado.',
                    code: Response::HTTP_UNPROCESSABLE_ENTITY,
                );
            }

            return $this->successResponse(
                data: RoleResource::make($item)->resolve(),
                message: 'Perfil atualizado com sucesso.',
            );
        } catch (Exception $exception) {
            Log::error($exception);
            return $this->errorResponse(message: 'Erro ao atualizar perfil.', code: Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function delete(int $id): JsonResponse
    {
        $defaultErrorMessage = 'Ocorreu um erro ao tentar excluir o perfil.';

        try {
            $response = $this->service->delete($id);

            if (!$response['success']) {
                return $this->errorResponse(message: $response['message'] ?? $defaultErrorMessage);
            }

            return $this->successResponse(message: 'Perfil excluído com sucesso.');
        } catch (Exception $exception) {
            Log::error($exception);
            return $this->errorResponse(message: $defaultErrorMessage, code: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
