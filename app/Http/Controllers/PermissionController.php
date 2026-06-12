<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginatedDataRequest;
use App\Http\Requests\Permission\Create;
use App\Http\Requests\Permission\DetailsOrDelete;
use App\Http\Requests\Permission\GetRolePermissions;
use App\Http\Requests\Permission\ListAll;
use App\Http\Requests\Permission\SyncRolePermissions;
use App\Http\Requests\Permission\Update;
use App\Services\Permission\PermissionServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    protected PermissionServiceInterface $service;

    public function __construct(PermissionServiceInterface $service)
    {
        $this->service = $service;
    }

    public function listAll(ListAll $request): JsonResponse
    {
        $response = $this->service->listAll($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getRolePermissions(GetRolePermissions $request): JsonResponse
    {
        $response = $this->service->getRolePermissions($request);
        return ApiResponseTransformer::respond($response);
    }

    public function syncRolePermissions(SyncRolePermissions $request): JsonResponse
    {
        $response = $this->service->syncRolePermissions($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getPermissions(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getPermissions($request);
        return ApiResponseTransformer::respond($response);
    }

    public function create(Create $request): JsonResponse
    {
        $response = $this->service->create($request);
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
}
