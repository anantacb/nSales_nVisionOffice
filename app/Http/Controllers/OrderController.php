<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\DetailsOrDelete;
use App\Http\Requests\Order\PaginatedDataRequest;
use App\Http\Requests\Order\ReExportOrExportPdf;
use App\Services\Order\OrderServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    protected OrderServiceInterface $service;

    public function __construct(OrderServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getOrders(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getOrders($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function getOpenOrders(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getOpenOrders($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function getFailedOrders(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getFailedOrders($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function details(DetailsOrDelete $request): JsonResponse
    {
        $response = $this->service->details($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function delete(DetailsOrDelete $request): JsonResponse
    {
        $response = $this->service->delete($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function getOrderOriginOptions(): JsonResponse
    {
        $response = $this->service->getOrderOriginOptions();
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function reExportOrder(ReExportOrExportPdf $request): JsonResponse
    {
        $response = $this->service->reExportOrder($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

}
