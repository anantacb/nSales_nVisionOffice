<?php

namespace App\Http\Controllers;

use App\Services\Module\ModuleServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    protected ModuleServiceInterface $moduleService;

    public function __construct(ModuleServiceInterface $moduleService)
    {
        $this->moduleService = $moduleService;
    }

    public function getAllModules(Request $request): JsonResponse
    {
        $response = $this->moduleService->getAllModules($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function getActivatedAndAvailableModulesByCompany(Request $request): JsonResponse
    {
        $response = $this->moduleService->getActivatedAndAvailableModulesByCompany($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function activateModule(Request $request): JsonResponse
    {
        $response = $this->moduleService->activateModule($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function deactivateModule(Request $request): JsonResponse
    {
        $response = $this->moduleService->deactivateModule($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }
}
