<?php

namespace App\Http\Controllers;

use App\Http\Requests\ModulePackage\Create;
use App\Http\Requests\ModulePackage\DetailsOrDelete;
use App\Http\Requests\ModulePackage\Update;
use App\Http\Requests\PaginatedDataRequest;
use App\Services\ModulePackage\ModulePackageServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModulePackageController extends Controller
{
    protected ModulePackageServiceInterface $service;

    public function __construct(ModulePackageServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getAllModulePackages(Request $request): JsonResponse
    {
        $response = $this->service->getAllModulePackages($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function getModulePackages(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getModulePackages($request);
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
