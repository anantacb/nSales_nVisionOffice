<?php

namespace App\Http\Controllers;

use App\Services\B2bGqlApi\B2bGqlApiService;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\Request;


class B2bGqlApiController extends Controller
{
    protected B2bGqlApiService $service;

    public function __construct(B2bGqlApiService $service)
    {
        $this->service = $service;
    }

    public function getItemGroupsAndItem(Request $request)
    {
        try {
            $response = $this->service->getItemGroupsAndItem($request);
            return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
        } catch (\Exception $e) {
            return ApiResponseTransformer::error("", $e->getMessage(), 400);
        }
    }
}
