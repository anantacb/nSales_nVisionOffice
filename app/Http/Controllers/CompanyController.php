<?php

namespace App\Http\Controllers;

use App\Http\Requests\Company\AssignableUserCompanies;
use App\Http\Requests\Company\Create;
use App\Http\Requests\Company\DetailsOrDelete;
use App\Http\Requests\Company\ModuleEnabledCompanies;
use App\Http\Requests\Company\Update;
use App\Http\Requests\PaginatedDataRequest;
use App\Services\Company\CompanyServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    protected CompanyServiceInterface $service;

    public function __construct(CompanyServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getAllCompanies(Request $request): JsonResponse
    {
        $response = $this->service->getAllCompanies($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function getModuleEnabledCompanies(ModuleEnabledCompanies $request): JsonResponse
    {
        $response = $this->service->getModuleEnabledCompanies($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function getAssignableCompaniesByUser(AssignableUserCompanies $request): JsonResponse
    {
        $response = $this->service->getAssignableCompaniesByUser($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function getCompanies(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getCompanies($request);
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
