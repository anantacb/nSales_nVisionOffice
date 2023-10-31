<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\Create;
use App\Http\Requests\Customer\DetailsOrDelete;
use App\Http\Requests\Customer\Update;
use App\Http\Requests\PaginatedDataRequest;
use App\Services\Customer\CustomerServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    protected CustomerServiceInterface $service;

    public function __construct(CustomerServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getCustomers(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getCustomers($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function create(Create $request): JsonResponse
    {
        $response = $this->service->create($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function update(Update $request): JsonResponse
    {
        $response = $this->service->update($request);
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
}
