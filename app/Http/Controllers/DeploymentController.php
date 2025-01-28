<?php

namespace App\Http\Controllers;

use App\Services\Deployment\DeploymentServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Exception;
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
        $status = $response->statusCode === 200 ? 'success' : 'error';
        return ApiResponseTransformer::{$status}($response->data, $response->message, $response->statusCode);
    }

    public function startCompanyDeployment(Request $request): JsonResponse
    {
        $response = $this->service->startCompanyDeployment($request);
        $status = $response->statusCode === 200 ? 'success' : 'error';
        return ApiResponseTransformer::{$status}($response->data, $response->message, $response->statusCode);
    }
}
