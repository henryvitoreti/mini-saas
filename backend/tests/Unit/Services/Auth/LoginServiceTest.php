<?php

namespace Tests\Unit\Services\Auth;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\Auth\LoginService;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

class LoginServiceTest extends UnitTestCase
{
    public static function sessions(): array
    {
        return ['normal session' => [false, 60], 'remembered session' => [true, 43200]];
    }

    #[DataProvider('sessions')]
    public function test_login_returns_token_context_and_updates_last_login(bool $remember, int $ttl): void
    {
        $this->freezeTime();
        $this->bindRequest();

        $prefix = 'tenant:host:'.sha1('example.test');

        Cache::shouldReceive('forget')->twice()->with($prefix.':company');
        Cache::shouldReceive('forget')->twice()->with($prefix.':company-permissions');
        Cache::shouldReceive('remember')->once()->with($prefix.':company', Mockery::any(), Mockery::type('Closure'))->andReturn(['id' => 1]);
        Cache::shouldReceive('remember')->once()->with($prefix.':company-permissions', Mockery::any(), Mockery::type('Closure'))->andReturn([['id' => 7]]);

        $factory = Mockery::mock();

        if (! $remember) {
            $factory->shouldReceive('getTTL')->once()->andReturn(60);
        } else {
            $factory->shouldNotReceive('getTTL');
        }

        $factory->shouldReceive('setTTL')->once()->with($ttl);

        $auth = Mockery::mock(Factory::class);
        $auth->shouldReceive('factory')->times($remember ? 1 : 2)->andReturn($factory);
        $auth->shouldReceive('attempt')->once()->with(['email' => 'ana@example.test', 'password' => 'secret', 'is_active' => true])->andReturn('jwt-token');

        $user = new User;
        $user->setRawAttributes(['id' => 5]);
        $auth->shouldReceive('user')->once()->andReturn($user);

        $this->app->instance('auth', $auth);

        $updated = new User;
        $updated->setRawAttributes(['id' => 5, 'last_login_at' => now()]);

        $repository = Mockery::mock(UserRepository::class);
        $repository->shouldReceive('updateLastLogin')->once()->with(5, Mockery::on(fn ($date) => $date->equalTo(now())))->andReturn($updated);

        $this->assertSame([
            'token_type' => 'Bearer', 'expires_in' => $ttl * 60, 'access_token' => 'jwt-token',
            'company' => ['id' => 1], 'permissions' => [['id' => 7]], 'user' => $updated,
        ], (new LoginService($repository))->login('ana@example.test', 'secret', $remember));
    }

    public function test_invalid_credentials_clear_stale_cache_and_do_not_update_user(): void
    {
        $this->bindRequest();

        $prefix = 'tenant:host:'.sha1('example.test');
        Cache::shouldReceive('forget')->once()->with($prefix.':company');
        Cache::shouldReceive('forget')->once()->with($prefix.':company-permissions');
        Cache::shouldNotReceive('remember');

        $factory = Mockery::mock();
        $factory->shouldReceive('getTTL')->once()->andReturn(60);
        $factory->shouldReceive('setTTL')->once()->with(60);

        $auth = Mockery::mock(Factory::class);
        $auth->shouldReceive('factory')->twice()->andReturn($factory);
        $auth->shouldReceive('attempt')->once()->with(['email' => 'bad', 'password' => 'wrong', 'is_active' => true])->andReturnFalse();
        $auth->shouldNotReceive('user');

        $this->app->instance('auth', $auth);

        $repository = Mockery::mock(UserRepository::class);
        $repository->shouldNotReceive('updateLastLogin');

        try {
            (new LoginService($repository))->login('bad', 'wrong');
            $this->fail('Expected invalid credentials to be rejected.');
        } catch (ValidationException $exception) {
            $this->assertSame(['email' => ['Credenciais inválidas.']], $exception->errors());
        }
    }
}
