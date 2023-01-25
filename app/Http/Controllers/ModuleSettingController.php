<?php

namespace App\Http\Controllers;

use App\Services\ModuleSetting\ModuleSettingServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModuleSettingController extends Controller
{
    protected ModuleSettingServiceInterface $moduleSettingService;

    public function __construct(ModuleSettingServiceInterface $moduleSettingService)
    {
        $this->moduleSettingService = $moduleSettingService;
    }

    public function getAllModuleSettingsByCompany(Request $request): JsonResponse
    {
        $response = $this->moduleSettingService->getAllModuleSettingsByCompanyId($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function updateModuleSettingsByCompany(Request $request): JsonResponse
    {
        $response = $this->moduleSettingService->updateModuleSettingsByCompanyId($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }
}
