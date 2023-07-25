<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmailConfiguration\Create;
use App\Http\Requests\EmailConfiguration\DetailsOrDelete;
use App\Http\Requests\EmailConfiguration\Update;
use App\Http\Requests\PaginatedDataRequest;
use App\Services\EmailConfiguration\EmailConfigurationServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

class EmailConfigurationController extends Controller
{
    protected EmailConfigurationServiceInterface $service;

    public function __construct(EmailConfigurationServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getEmailConfigurations(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getEmailConfigurations($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function getCompanyEmailConfigurations(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getCompanyEmailConfigurations($request);
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
}
