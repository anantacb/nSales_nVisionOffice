<?php

namespace App\Http\Controllers;

use App\Services\WebShopText\WebShopTextServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebShopTextController extends Controller
{
    protected WebShopTextServiceInterface $service;

    public function __construct(WebShopTextServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getByItem(Request $request): JsonResponse
    {
        $response = $this->service->getByItem($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

}
