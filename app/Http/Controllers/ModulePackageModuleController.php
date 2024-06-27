<?php

namespace App\Http\Controllers;

use App\Http\Requests\ModulePackageModule\Create;
use App\Http\Requests\ModulePackageModule\DetailsOrDelete;
use App\Services\ModulePackageModule\ModulePackageModuleServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

class ModulePackageModuleController extends Controller
{
    protected ModulePackageModuleServiceInterface $service;

    public function __construct(ModulePackageModuleServiceInterface $service)
    {
        $this->service = $service;
    }

    public function delete(DetailsOrDelete $request): JsonResponse
    {
        $response = $this->service->delete($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function create(Create $request): JsonResponse
    {
        $response = $this->service->create($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }
}
