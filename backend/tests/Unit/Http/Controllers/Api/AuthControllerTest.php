<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Http\Controllers\Api\AuthController;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\Auth\LoginService;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Unit\UnitTestCase;

class AuthControllerTest extends UnitTestCase
{
    public function test_login_serializes_user_without_password(): void
    {
        $this->bindRequest();
        $user = new User;
        $user->setRawAttributes(['id' => 1, 'name' => 'Ana', 'email' => 'ana@example.test', 'password' => 'secret', 'is_active' => true]);

        $service = $this->createMock(LoginService::class);
        $service->expects($this->once())->method('login')->with('ana@example.test', 'secret', true)
            ->willReturn(['access_token' => 'token', 'user' => $user]);

        $request = LoginRequest::create('/login', 'POST', ['email' => 'ana@example.test', 'password' => 'secret', 'remember_login' => true]);
        $response = (new AuthController($service))->login($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['access_token' => 'token', 'user' => [
            'id' => 1, 'name' => 'Ana', 'email' => 'ana@example.test', 'phone' => null, 'is_active' => true,
        ]], $response->getData(true)['data']);
    }

    public function test_login_returns_validation_errors(): void
    {
        $exception = ValidationException::withMessages(['email' => ['Credenciais inválidas.']]);
        $service = $this->createMock(LoginService::class);
        $service->expects($this->once())->method('login')->with('bad', 'secret', false)->willThrowException($exception);

        $response = (new AuthController($service))->login(LoginRequest::create('/login', 'POST', ['email' => 'bad', 'password' => 'secret']));

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(['email' => ['Credenciais inválidas.']], $response->getData(true)['errors']);
    }

    public function test_login_logs_unexpected_errors_without_exposing_details(): void
    {
        $service = $this->createMock(LoginService::class);
        $service->expects($this->once())->method('login')->willThrowException(new RuntimeException('private detail'));

        Log::shouldReceive('error')->once()->with('private detail');
        $response = (new AuthController($service))->login(new LoginRequest);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Ocorreu um erro ao processar a requisição.', $response->getData(true)['message']);
    }

    public static function logoutResults(): array
    {
        return [[false], [true]];
    }

    #[DataProvider('logoutResults')]
    public function test_logout_clears_cache_and_handles_auth_failure(bool $fails): void
    {
        $this->bindRequest();
        $key = 'tenant:host:'.sha1('example.test');
        Cache::shouldReceive('forget')->once()->with($key.':company-permissions');

        $auth = Mockery::mock(Factory::class);
        $expectation = $auth->shouldReceive('logout')->once()->withNoArgs();

        $this->app->instance('auth', $auth);

        if ($fails) {
            $expectation->andThrow(new RuntimeException('logout failed'));
            Log::shouldReceive('error')->once()->with('logout failed');
            Cache::shouldReceive('forget')->with($key.':company')->never();
        } else {
            Cache::shouldReceive('forget')->once()->with($key.':company');
        }

        $response = (new AuthController($this->createStub(LoginService::class)))->logout();
        $this->assertSame($fails ? 400 : 200, $response->getStatusCode());
    }

    public function test_check_returns_no_content_status(): void
    {
        $this->assertSame(204, (new AuthController($this->createStub(LoginService::class)))->check()->getStatusCode());
    }

    public function test_refresh_permissions_invalidates_cached_permissions(): void
    {
        $this->bindRequest();
        $key = 'tenant:host:'.sha1('example.test').':company-permissions';
        $permissions = [['id' => 7, 'is_active' => true]];

        Cache::shouldReceive('forget')->once()->with($key)->ordered();
        Cache::shouldReceive('remember')->once()->with($key, Mockery::any(), Mockery::type('Closure'))->ordered()->andReturn($permissions);

        $response = (new AuthController($this->createStub(LoginService::class)))->refreshPermissions();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['permissions' => $permissions], $response->getData(true)['data']);
    }
}
