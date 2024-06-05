<?php

namespace App\Http\Controllers;

use App\Http\Requests\Item\DetailsOrDelete;
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

    public function getItemAttributesByItem(DetailsOrDelete $request): JsonResponse
    {
        $response = $this->service->getItemAttributesByItem($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

}
