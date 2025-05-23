<?php

namespace App\Http\Controllers;

use App\Http\Requests\WebShopText\GetByItem;
use App\Http\Requests\WebShopText\UpdateOrCreateByItem;
use App\Services\WebShopText\WebShopTextServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

class WebShopTextController extends Controller
{
    protected WebShopTextServiceInterface $service;

    public function __construct(WebShopTextServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getByItem(GetByItem $request): JsonResponse
    {
        $response = $this->service->getByItem($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function updateByItem(UpdateOrCreateByItem $request): JsonResponse
    {
        $response = $this->service->updateByItem($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

}
