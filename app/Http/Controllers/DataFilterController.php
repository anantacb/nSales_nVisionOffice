<?php

namespace App\Http\Controllers;

use App\Http\Requests\DataFilter\Create;
use App\Http\Requests\DataFilter\DetailsOrDelete;
use App\Http\Requests\DataFilter\GetFilterResult;
use App\Http\Requests\DataFilter\Update;
use App\Http\Requests\PaginatedDataRequest;
use App\Services\DataFilter\DataFilterServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

class DataFilterController extends Controller
{
    protected DataFilterServiceInterface $service;

    public function __construct(DataFilterServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getDataFilters(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getDataFilters($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }


    public function getCompanyDataFilters(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getCompanyDataFilters($request);
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

    public function delete(DetailsOrDelete $request): JsonResponse
    {
        $response = $this->service->delete($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function details(DetailsOrDelete $request): JsonResponse
    {
        $response = $this->service->details($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function getFilterResult(GetFilterResult $request): JsonResponse
    {
        $response = $this->service->getFilterResult($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }
}
