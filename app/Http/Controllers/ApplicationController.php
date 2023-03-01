<?php

namespace App\Http\Controllers;

use App\Services\Application\ApplicationServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    protected ApplicationServiceInterface $applicationService;

    public function __construct(ApplicationServiceInterface $applicationService)
    {
        $this->applicationService = $applicationService;
    }

    public function getAllApplications(Request $request): JsonResponse
    {
        $response = $this->applicationService->getAllApplications($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }
}
