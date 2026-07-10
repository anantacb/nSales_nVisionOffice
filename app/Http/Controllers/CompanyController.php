<?php

namespace App\Http\Controllers;

use App\Http\Requests\Company\AddCustomDomain;
use App\Http\Requests\Company\AssignableUserCompanies;
use App\Http\Requests\Company\CloneCompany;
use App\Http\Requests\Company\Create;
use App\Http\Requests\Company\DeleteCustomDomain;
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
        return ApiResponseTransformer::respond($response);
    }

    public function getAuthUserCompanies(Request $request): JsonResponse
    {
        $response = $this->service->getAuthUserCompanies($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getModuleEnabledCompanies(ModuleEnabledCompanies $request): JsonResponse
    {
        $response = $this->service->getModuleEnabledCompanies($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getAssignableCompaniesByUser(AssignableUserCompanies $request): JsonResponse
    {
        $response = $this->service->getAssignableCompaniesByUser($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getCompanies(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getCompanies($request);
        return ApiResponseTransformer::respond($response);
    }

    public function create(Create $request): JsonResponse
    {
        $response = $this->service->create($request);
        return ApiResponseTransformer::respond($response);
    }

    public function cloneCompany(CloneCompany $request): JsonResponse
    {
        $response = $this->service->cloneCompany($request);
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

    public function getCompanyCustomDomains(Request $request): JsonResponse
    {
        $response = $this->service->getCompanyCustomDomains($request);
        return ApiResponseTransformer::respond($response);
    }

    public function addCompanyCustomDomain(AddCustomDomain $request): JsonResponse
    {
        $response = $this->service->addCompanyCustomDomain($request);
        return ApiResponseTransformer::respond($response);
    }

    public function deleteCompanyCustomDomain(DeleteCustomDomain $request): JsonResponse
    {
        $response = $this->service->deleteCompanyCustomDomain($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getPostmarkServer(Request $request): JsonResponse
    {
        $response = $this->service->getPostmarkServer($request);
        return ApiResponseTransformer::respond($response);
    }
    public function createPostmarkServer(Request $request): JsonResponse
    {
        $response = $this->service->createPostmarkServer($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getImageHostAccount(Request $request): JsonResponse
    {
        $response = $this->service->getImageHostAccount($request);
        return ApiResponseTransformer::respond($response);
    }

    public function createImageHostAccount(Request $request): JsonResponse
    {
        $response = $this->service->createImageHostAccount($request);
        return ApiResponseTransformer::respond($response);
    }
}
