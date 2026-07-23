<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CompanyHelper;
use App\Helpers\CompanyPermissionHelper;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\LoginService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiBaseController
{
    public function __construct(private readonly LoginService $loginService)
    {}

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $response = $this->loginService->login(
                (string) $request->input('email'),
                (string) $request->input('password'),
                (bool) $request->input('remember_login')
            );
            if (isset($response['user'])) {
                $response['user'] = UserResource::make($response['user'])->resolve();
            }

            return $this->successResponse(data: $response);
        } catch (ValidationException $validationException) {
            return $this->errorResponse(
                errors: $validationException->errors(),
                message: $validationException->getMessage(),
                code: $validationException->status
            );
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return $this->errorResponse();
        }
    }

    public function logout(): JsonResponse
    {
        try {
            CompanyPermissionHelper::forgetCurrentPermissions();
            auth()->logout();
            CompanyHelper::forgetCurrentCompanyResource();

            return $this->successResponse(message: 'Logout realizado com sucesso.');
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return $this->errorResponse();
        }
    }

    public function check(): JsonResponse
    {
        $company = CompanyHelper::getCurrentCompanyResource();
        $permissions = CompanyPermissionHelper::rememberCurrentPermissions();
        $authenticatedUser = auth()->user();
        $user = UserResource::make($authenticatedUser)->resolve();

        return $this->successResponse(data: [
            'user' => $user,
            'company' => $company,
            'permissions' => $permissions,
        ]);
    }
}
