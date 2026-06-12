<?php

namespace App\Http\Controllers;

use App\Http\Requests\Language\Create;
use App\Http\Requests\Language\DetailsOrDelete;
use App\Http\Requests\Language\Update;
use App\Http\Requests\PaginatedDataRequest;
use App\Services\Language\LanguageServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    protected LanguageServiceInterface $service;

    public function __construct(LanguageServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getAllLanguages(Request $request): JsonResponse
    {
        $response = $this->service->getAllLanguages($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getLanguages(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getLanguages($request);
        return ApiResponseTransformer::respond($response);
    }

    public function create(Create $request): JsonResponse
    {
        $response = $this->service->create($request);
        return ApiResponseTransformer::respond($response);
    }

    public function update(Update $request): JsonResponse
    {
        $response = $this->service->update($request);
        return ApiResponseTransformer::respond($response);
    }

    public function details(DetailsOrDelete $request): JsonResponse
    {
        $response = $this->service->details($request);
        return ApiResponseTransformer::respond($response);
    }

    public function delete(DetailsOrDelete $request): JsonResponse
    {
        $response = $this->service->delete($request);
        return ApiResponseTransformer::respond($response);
    }
}
