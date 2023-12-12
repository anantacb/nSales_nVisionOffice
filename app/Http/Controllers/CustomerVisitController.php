<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginatedDataRequest;
use App\Services\CustomerVisit\CustomerVisitServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerVisitController extends Controller
{
    protected CustomerVisitServiceInterface $service;

    public function __construct(CustomerVisitServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getCustomerVisits(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getCustomerVisits($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function getDistinctValue(Request $request): JsonResponse
    {
        $response = $this->service->getDistinctValue($request->columnName);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

}
