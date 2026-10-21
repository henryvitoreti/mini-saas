<?php

namespace Tests\Unit;

use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

/** Direct method tests: no HTTP dispatch, migrations or persisted fixtures. */
abstract class UnitTestCase extends TestCase
{
    protected function bindRequest(array $data = []): Request
    {
        $request = Request::create('https://example.test/api', 'GET', $data);
        $this->app->instance('request', $request);

        return $request;
    }

    protected function validatedRequest(string $class, array $data): Request
    {
        $request = Mockery::mock($class)->makePartial();
        $request->initialize();
        $request->merge([...$data, 'unvalidated' => 'ignored']);
        $request->shouldReceive('validated')->once()->withNoArgs()->andReturn($data);

        $this->app->instance('request', $request);

        return $request;
    }
}
