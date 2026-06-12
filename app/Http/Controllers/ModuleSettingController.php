<?php

namespace App\Http\Controllers;

use App\Http\Requests\ModuleSetting\CoreModuleSettings;
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
        return ApiResponseTransformer::respond($response);
    }


    public function getModuleSettings(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getModuleSettings($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getModuleSettingsByName(Request $request)
    {
        $response = $this->service->getModuleSettingsByName($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getCoreModuleSettingsByName(CoreModuleSettings $request)
    {
        $response = $this->service->getCoreModuleSettingsByName($request);
        return ApiResponseTransformer::respond($response);
    }

    public function updateModuleSettingsByCompany(Request $request): JsonResponse
    {
        $response = $this->service->updateModuleSettingsByCompanyId($request);
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
