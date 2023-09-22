<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplicationModule\Create;
use App\Http\Requests\ApplicationModule\DetailsOrDelete;
use App\Http\Requests\ApplicationModule\Update;
use App\Services\ApplicationModule\ApplicationModuleServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

class ApplicationModuleController extends Controller
{
    protected ApplicationModuleServiceInterface $service;

    public function __construct(ApplicationModuleServiceInterface $service)
    {
        $this->service = $service;
    }

    public function delete(DetailsOrDelete $request): JsonResponse
    {
        $response = $this->service->delete($request);
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
}
