<?php

namespace App\Services\Auth;

use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use JsonException;

class LoginService
{
    public function __construct(private readonly UserRepository $userRepository)
    {}

    /**
     * @return array<string, mixed>
     * @throws ValidationException|JsonException
     */
    public function login(string $email, string $password): array
    {
        $token = auth()->attempt([
            'email' => $email,
            'password' => $password,
            'is_active' => true,
        ]);

        if ($token === false) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        $user = auth()->user();

        $user = $this->userRepository->updateLastLogin($user->id, now());

        return [
            'token_type' => 'Bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'access_token' => $token,
            'user' => new UserResource($user),
        ];
    }
}
