<?php

namespace App\Http\Controllers;

use App\Services\Deployment\DeploymentServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class DeploymentController extends Controller
{
    protected DeploymentServiceInterface $service;

    public function __construct(DeploymentServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getCompanyDeploymentStatus(Request $request): JsonResponse
    {
        $response = $this->service->getCompanyDeploymentStatus($request);
        return ApiResponseTransformer::respond($response);
    }

    public function startCompanyDeployment(Request $request): JsonResponse
    {
        $response = $this->service->startCompanyDeployment($request);
        return ApiResponseTransformer::respond($response);
    }
}
