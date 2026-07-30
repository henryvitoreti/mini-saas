<?php

namespace App\Services;

use App\Repositories\PermissionRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class PermissionService
{
    public function __construct(private readonly PermissionRepository $repository) {}

    public function search(Request $request): Collection
    {
        return $this->repository->getSearch($request);
    }
}
