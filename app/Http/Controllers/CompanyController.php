<?php

namespace App\Http\Controllers;

use App\Http\Requests\Company\ModuleEnabledCompanies;
use App\Services\Company\CompanyServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    protected CompanyServiceInterface $companyService;

    public function __construct(CompanyServiceInterface $companyService)
    {
        $this->companyService = $companyService;
    }

    public function getAllCompanies(Request $request): JsonResponse
    {
        $response = $this->companyService->getAllCompanies($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function getModuleEnabledCompanies(ModuleEnabledCompanies $request): JsonResponse
    {
        $response = $this->companyService->getModuleEnabledCompanies($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }
}
