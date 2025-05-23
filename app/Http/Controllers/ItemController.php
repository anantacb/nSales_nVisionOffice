<?php

namespace App\Http\Controllers;

use App\Http\Requests\Item\DetailsOrDelete;
use App\Http\Requests\Item\Update;
use App\Http\Requests\PaginatedDataRequest;
use App\Services\Item\ItemServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

class ItemController extends Controller
{
    protected ItemServiceInterface $service;

    public function __construct(ItemServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getItems(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getItems($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function details(DetailsOrDelete $request): JsonResponse
    {
        $response = $this->service->details($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function update(Update $request): JsonResponse
    {
        $response = $this->service->update($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

}
