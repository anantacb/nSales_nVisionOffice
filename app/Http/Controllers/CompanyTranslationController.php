<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyTranslation\Create;
use App\Http\Requests\CompanyTranslation\DetailsOrDelete;
use App\Http\Requests\CompanyTranslation\Update;
use App\Http\Requests\PaginatedDataRequest;
use App\Services\CompanyTranslation\CompanyTranslationServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

class CompanyTranslationController extends Controller
{
    protected CompanyTranslationServiceInterface $service;

    public function __construct(CompanyTranslationServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getCompanyTranslations(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getCompanyTranslations($request);
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
