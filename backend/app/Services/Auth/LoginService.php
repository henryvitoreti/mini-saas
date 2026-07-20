<?php

namespace App\Services\Auth;

use App\Helpers\CompanyHelper;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Validation\ValidationException;
use JsonException;

class LoginService
{
    private const int EXTENDED_TTL_MINUTES = 43200;

    public function __construct(private readonly UserRepository $userRepository)
    {}

    /**
     * @throws ValidationException|JsonException
     */
    public function login(string $email, string $password, bool $rememberLogin = false): array
    {
        CompanyHelper::forgetCurrentCompanyResource();

        $ttl = $rememberLogin ? self::EXTENDED_TTL_MINUTES : auth()->factory()->getTTL();
        auth()->factory()->setTTL($ttl);

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
        CompanyHelper::rememberCurrentCompanyResource();

        return [
            'token_type' => 'Bearer',
            'expires_in' => $ttl * 60,
            'access_token' => $token,
            'user' => $user,
        ];
    }
}
