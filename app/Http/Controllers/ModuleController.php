<?php

namespace App\Http\Controllers;

use App\Http\Requests\Module\Create;
use App\Http\Requests\Module\DetailsOrDelete;
use App\Http\Requests\Module\GetModulesByApplication;
use App\Http\Requests\Module\GetModulesByModulePackage;
use App\Http\Requests\Module\Update;
use App\Http\Requests\PaginatedDataRequest;
use App\Services\Module\ModuleServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    protected ModuleServiceInterface $service;

    public function __construct(ModuleServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getModules(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getModules($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getAllModules(Request $request): JsonResponse
    {
        $response = $this->service->getAllModules($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getActivatedAndAvailableModulesByCompany(Request $request): JsonResponse
    {
        $response = $this->service->getActivatedAndAvailableModulesByCompany($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getActivatedModulesByCompany(Request $request): JsonResponse
    {
        $response = $this->service->getActivatedModulesByCompany($request);
        return ApiResponseTransformer::respond($response);
    }

    public function activateModule(Request $request): JsonResponse
    {
        $response = $this->service->activateModule($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getModulesByApplication(GetModulesByApplication $request): JsonResponse
    {
        $response = $this->service->getModulesByApplication($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getAssignableModulesByApplication(GetModulesByApplication $request): JsonResponse
    {
        $response = $this->service->getAssignableModulesByApplication($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getAssignableModulesByModulePackage(GetModulesByModulePackage $request): JsonResponse
    {
        $response = $this->service->getAssignableModulesByModulePackage($request);
        return ApiResponseTransformer::respond($response);
    }

    public function deactivateModule(Request $request): JsonResponse
    {
        $response = $this->service->deactivateModule($request);
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
