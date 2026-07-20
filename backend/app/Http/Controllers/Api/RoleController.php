<?php

namespace App\Http\Controllers\Api;

use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends ApiBaseController
{
    public function __construct(private readonly RoleService $roleService)
    {}

    public function options(Request $request): JsonResponse
    {
        $options = $this->roleService->options($request);
        return $this->successResponse(data: compact('options'));
    }
}
