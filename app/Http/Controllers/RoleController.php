<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role\GetRoles;
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
}
