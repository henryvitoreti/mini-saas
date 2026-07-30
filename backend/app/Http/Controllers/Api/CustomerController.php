<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerController extends ApiBaseController
{
    public function __construct(private readonly CustomerService $service)
    {}

    public function index(Request $request): JsonResponse
    {
        $this->useSimpleResourceForList($request);

        $items = $this->service->index($request);
        $data = $this->paginatedResponse($items, CustomerResource::class);

        return $this->successResponse(data: $data);
    }

    public function search(Request $request): JsonResponse
    {
        $this->useSimpleResourceForList($request);

        $items = $this->service->search($request);
        $data = CustomerResource::collection($items)->resolve();

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
        $data = CustomerResource::make($item)->resolve();

        return $this->successResponse(data: $data);
    }

    public function store(CustomerRequest $request): JsonResponse
    {
        $item = $this->service->store($request->validated());
        $data = CustomerResource::make($item)->resolve();

        return $this->successResponse(
            data: $data,
            message: 'Cliente cadastrado com sucesso.',
            code: Response::HTTP_CREATED
        );
    }

    public function update(CustomerRequest $request, int $id): JsonResponse
    {
        $item = $this->service->update($id, $request->validated());

        if ($item === null) {
            return $this->errorResponse(message: 'Cliente não encontrado.', code: Response::HTTP_NOT_FOUND);
        }

        $data = CustomerResource::make($item)->resolve();

        return $this->successResponse(
            data: $data,
            message: 'Cliente atualizado com sucesso.'
        );
    }

    public function delete(int $id): JsonResponse
    {
        if (!$this->service->delete($id)) {
            return $this->errorResponse(message: 'Cliente não encontrado.', code: Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse(message: 'Cliente excluído com sucesso.');
    }
}
