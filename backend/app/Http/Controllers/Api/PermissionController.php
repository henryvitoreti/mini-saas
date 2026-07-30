<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PermissionResource;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends ApiBaseController
{
    public function __construct(private readonly PermissionService $service) {}

    public function search(Request $request): JsonResponse
    {
        $items = $this->service->search($request);
        $data = PermissionResource::collection($items)->resolve();

        return $this->successResponse(data: $data);
    }
}
