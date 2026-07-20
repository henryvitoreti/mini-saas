<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\TenantRequest;
use App\Http\Resources\TenantResource;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TenantController extends ApiBaseController
{
    public function __construct(private readonly TenantService $tenantService)
    {}

    public function index(Request $request): JsonResponse
    {
        $this->useSimpleResourceForList($request);

        $tenants = $this->tenantService->index($request);
        $data = $this->paginatedResponse($tenants, TenantResource::class);

        return $this->successResponse(data: $data);
    }

    public function show(string $id): JsonResponse
    {
        $tenant = $this->tenantService->show($id);
        $data = TenantResource::make($tenant)->resolve();

        return $this->successResponse(data: $data);
    }

    public function store(TenantRequest $request): JsonResponse
    {
        try {
            $tenant = $this->tenantService->store($request->all());
            $data = TenantResource::make($tenant)->resolve();

            return $this->successResponse(
                data: $data,
                message: 'Domínio cadastrado com sucesso.',
                code: Response::HTTP_CREATED
            );
        } catch (RuntimeException $exception) {
            return $this->errorResponse(message: $exception->getMessage());
        } catch (Throwable $e) {
            return $this->errorResponse(message: 'Não foi possível criar o domínio.');
        }
    }

    public function update(TenantRequest $request, string $id): JsonResponse
    {
        try {
            $tenant = $this->tenantService->update($id, $request->validated());
            $data = TenantResource::make($tenant)->resolve();

            return $this->successResponse(
                data: $data,
                message: 'Domínio atualizado com sucesso.'
            );
        } catch (RuntimeException $exception) {
            return $this->errorResponse(message: $exception->getMessage());
        } catch (Throwable $e) {
            return $this->errorResponse(message: 'Não foi possível atualizar o domínio.');
        }
    }
}
