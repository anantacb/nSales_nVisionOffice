<?php

namespace App\Http\Controllers;

use App\Services\WebShopUser\WebShopUserServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\Request;

class WebShopUserController extends Controller
{
    protected WebShopUserServiceInterface $service;

    public function __construct(WebShopUserServiceInterface $service)
    {
        $this->service = $service;
    }

    public function details(Request $request)
    {
        $response = $this->service->details($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function createTestUser(Request $request)
    {
        try {
            $response = $this->service->createTestUser($request);
            return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
        } catch (\Exception $e) {
            return ApiResponseTransformer::error("", $e->getMessage(), 400);
        }
    }
}
