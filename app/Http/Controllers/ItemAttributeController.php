<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemAttribute\Delete;
use App\Http\Requests\ItemAttribute\DetailsByItem;
use App\Http\Requests\ItemAttribute\Update;
use App\Services\ItemAttribute\ItemAttributeService;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

class ItemAttributeController extends Controller
{
    protected ItemAttributeService $service;

    public function __construct(ItemAttributeService $service)
    {
        $this->service = $service;
    }

    public function getItemAttributesByItem(DetailsByItem $request): JsonResponse
    {
        $response = $this->service->getItemAttributesByItem($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function updateItemAttributesByItem(Update $request): JsonResponse
    {
        $response = $this->service->updateItemAttributesByItem($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function delete(Delete $request): JsonResponse
    {
        $response = $this->service->delete($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

}
