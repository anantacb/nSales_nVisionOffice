<?php

namespace App\Http\Controllers;

use App\Http\Requests\ModuleSetting\Create;
use App\Http\Requests\ModuleSetting\DetailsOrDelete;
use App\Http\Requests\ModuleSetting\Update;
use App\Http\Requests\PaginatedDataRequest;
use App\Services\ModuleSetting\ModuleSettingServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModuleSettingController extends Controller
{
    protected ModuleSettingServiceInterface $service;

    public function __construct(ModuleSettingServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getAllModuleSettingsByCompany(Request $request): JsonResponse
    {
        $response = $this->service->getAllModuleSettingsByCompanyId($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }


    public function getModuleSettings(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getModuleSettings($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function updateModuleSettingsByCompany(Request $request): JsonResponse
    {
        $response = $this->service->updateModuleSettingsByCompanyId($request);
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
