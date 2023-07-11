<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginatedDataRequest;
use App\Http\Requests\Role\Create;
use App\Http\Requests\Role\DetailsOrDelete;
use App\Http\Requests\Role\GetRoles;
use App\Http\Requests\Role\Update;
use App\Services\Role\RoleServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    protected RoleServiceInterface $service;

    public function __construct(RoleServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getRolesByCompany(GetRoles $request): JsonResponse
    {
        $response = $this->service->getAssignableRolesByCompany($request);

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

    public function getCompanyRoles(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getCompanyRoles($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }
}
