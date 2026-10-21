<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiBaseController;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\Unit\UnitTestCase;

class ApiBaseControllerTest extends UnitTestCase
{
    public function test_success_response_defaults(): void
    {
        $response = (new ApiBaseController)->successResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['message' => 'Operação realizada com sucesso.', 'data' => []], $response->getData(true));
    }

    public function test_success_response_preserves_custom_payload_message_and_status(): void
    {
        $response = (new ApiBaseController)->successResponse(['id' => 7], 'Created', 201);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame(['message' => 'Created', 'data' => ['id' => 7]], $response->getData(true));
    }

    public function test_error_response_defaults(): void
    {
        $response = (new ApiBaseController)->errorResponse();

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame(['message' => 'Ocorreu um erro ao processar a requisição.', 'errors' => []], $response->getData(true));
    }

    public function test_error_response_preserves_validation_errors(): void
    {
        $errors = ['name' => ['Required']];
        $response = (new ApiBaseController)->errorResponse($errors, 'Invalid', 422);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(['message' => 'Invalid', 'errors' => $errors], $response->getData(true));
    }

    public function test_pagination_without_resource_preserves_items_and_navigation(): void
    {
        $controller = new class extends ApiBaseController
        {
            public function paginate(LengthAwarePaginator $paginator): array
            {
                return $this->paginatedResponse($paginator);
            }
        };

        $paginator = new LengthAwarePaginator([['id' => 7]], 3, 1, 2, ['path' => '/items']);
        $data = $controller->paginate($paginator);

        $this->assertSame($paginator->getCollection(), $data['items']);

        unset($data['items']);

        $this->assertSame(['current_page' => 2, 'last_page' => 3, 'per_page' => 1, 'total' => 3,
            'from' => 2, 'to' => 2, 'prev_page_url' => '/items?page=1', 'next_page_url' => '/items?page=3'], $data);
    }
}
