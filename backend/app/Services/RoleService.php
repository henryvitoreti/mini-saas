<?php

namespace App\Services;

use App\Repositories\RoleRepository;
use Illuminate\Http\Request;

class RoleService
{
    public function __construct(private readonly RoleRepository $roleRepository)
    {}

    public function options(Request $request): array
    {
        return $this->roleRepository->getOptions($request);
    }
}
