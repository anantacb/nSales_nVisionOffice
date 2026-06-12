<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginatedDataRequest;
use App\Http\Requests\User\AssignToCompany;
use App\Http\Requests\User\CreateCompanyUser;
use App\Http\Requests\User\DetailsOrDelete;
use App\Http\Requests\User\DetailsOrDeleteCompanyUser;
use App\Http\Requests\User\GetAllCompanyUsers;
use App\Http\Requests\User\Update;
use App\Http\Requests\User\UpdateCompanyUser;
use App\Http\Requests\User\UpdateCompanyUserInitials;
use App\Http\Requests\User\UpdateCompanyUserRoles;
use App\Services\User\UserServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserServiceInterface $service;

    public function __construct(UserServiceInterface $service)
    {
        $this->service = $service;
    }

    public function authUserDetails(): JsonResponse
    {
        $response = $this->service->authUserDetails();
        return ApiResponseTransformer::respond($response);
    }

    public function getAllCompanyUsers(GetAllCompanyUsers $request): JsonResponse
    {
        $response = $this->service->getAllCompanyUsers($request);

        return ApiResponseTransformer::respond($response);
    }

    public function getDevelopers(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getDevelopers($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getUsers(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getUsers($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getCompanyUsers(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getCompanyUsers($request);
        return ApiResponseTransformer::respond($response);
    }

    public function createCompanyUser(CreateCompanyUser $request): JsonResponse
    {
        $response = $this->service->createCompanyUser($request);

        return ApiResponseTransformer::respond($response);
    }

    public function details(DetailsOrDelete $request): JsonResponse
    {
        $response = $this->service->details($request);

        return ApiResponseTransformer::respond($response);
    }

    public function companyUserDetails(DetailsOrDeleteCompanyUser $request): JsonResponse
    {
        $response = $this->service->companyUserDetails($request);

        return ApiResponseTransformer::respond($response);
    }

    public function update(Update $request): JsonResponse
    {
        $response = $this->service->update($request);

        return ApiResponseTransformer::respond($response);
    }

    public function tagDeveloperToAllCompanies(Request $request): JsonResponse
    {
        $response = $this->service->tagDeveloperToAllCompanies($request);

        return ApiResponseTransformer::respond($response);
    }

    public function updateCompanyUser(UpdateCompanyUser $request): JsonResponse
    {
        $response = $this->service->updateCompanyUser($request);

        return ApiResponseTransformer::respond($response);
    }

    public function updateCompanyUserRoles(UpdateCompanyUserRoles $request): JsonResponse
    {
        $response = $this->service->updateCompanyUserRoles($request);

        return ApiResponseTransformer::respond($response);
    }

    public function updateCompanyUserInitials(UpdateCompanyUserInitials $request): JsonResponse
    {
        $response = $this->service->updateCompanyUserInitials($request);

        return ApiResponseTransformer::respond($response);
    }

    public function assignToCompany(AssignToCompany $request): JsonResponse
    {
        $response = $this->service->assignToCompany($request);

        return ApiResponseTransformer::respond($response);
    }
}
