<?php

namespace App\Http\Controllers\Api;

use App\Constants\RequestAttribute;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ApiBaseController extends Controller
{
    use ApiResponseTrait;

    protected function useSimpleResourceForList(Request $request): void
    {
        $request->attributes->set(RequestAttribute::USE_SIMPLE_RESOURCE_RESPONSE, !$request->boolean('with_details'));
    }
}
