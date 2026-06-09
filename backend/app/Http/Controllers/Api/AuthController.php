<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginRequest;
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
            $response = $this->loginService->login($request->input('email'), $request->input('password'));
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
            auth()->logout();
            return $this->successResponse(message: 'Logout realizado com sucesso.');
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return $this->errorResponse();
        }
    }
}
