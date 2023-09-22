<?php

namespace App\Http\Controllers;

use App\Http\Requests\Application\Create;
use App\Http\Requests\Application\DetailsOrDelete;
use App\Http\Requests\Application\Update;
use App\Http\Requests\PaginatedDataRequest;
use App\Services\Application\ApplicationServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    protected ApplicationServiceInterface $service;

    public function __construct(ApplicationServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getAllApplications(Request $request): JsonResponse
    {
        $response = $this->service->getAllApplications($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function getApplications(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getApplications($request);
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
