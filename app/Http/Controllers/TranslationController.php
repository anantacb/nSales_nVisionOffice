<?php

namespace App\Http\Controllers;

use App\Http\Requests\Translation\Create;
use App\Http\Requests\Translation\DetailsOrDelete;
use App\Http\Requests\Translation\Update;
use App\Http\Requests\PaginatedDataRequest;
use App\Services\Translation\TranslationServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

class TranslationController extends Controller
{
    protected TranslationServiceInterface $service;

    public function __construct(TranslationServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getTranslations(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getTranslations($request);
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
