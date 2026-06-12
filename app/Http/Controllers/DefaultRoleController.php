<?php

namespace App\Http\Controllers;

use App\Http\Requests\DefaultRole\Create;
use App\Http\Requests\DefaultRole\DetailsOrDelete;
use App\Http\Requests\DefaultRole\Update;
use App\Http\Requests\PaginatedDataRequest;
use App\Services\DefaultRole\DefaultRoleServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

class DefaultRoleController extends Controller
{
    protected DefaultRoleServiceInterface $service;

    public function __construct(DefaultRoleServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getDefaultRoles(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getDefaultRoles($request);
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
