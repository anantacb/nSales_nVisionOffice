<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\ByItem\SalesOrOrders;
use App\Services\Order\OrderByItemServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

class OrderByItemController extends Controller
{
    protected OrderByItemServiceInterface $service;

    public function __construct(OrderByItemServiceInterface $service)
    {
        $this->service = $service;
    }

    public function totalSalesYearlyByItem(SalesOrOrders $request): JsonResponse
    {
        $response = $this->service->totalSalesYearly($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function totalSalesMonthlyByItem(SalesOrOrders $request): JsonResponse
    {
        $response = $this->service->totalSalesMonthly($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function totalQuantityYearlyByItem(SalesOrOrders $request): JsonResponse
    {
        $response = $this->service->totalQuantityYearly($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function totalQuantityMonthlyByItem(SalesOrOrders $request): JsonResponse
    {
        $response = $this->service->totalQuantityMonthly($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

}
