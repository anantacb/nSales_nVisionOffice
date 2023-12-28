<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\SalesOrDelete;
use App\Services\Order\OrderByCustomerServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

class OrderByCustomerController extends Controller
{
    protected OrderByCustomerServiceInterface $service;

    public function __construct(OrderByCustomerServiceInterface $service)
    {
        $this->service = $service;
    }

    public function latestOrdersByCustomer(SalesOrDelete $request): JsonResponse
    {
        $response = $this->service->latestOrdersByCustomer($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

}
