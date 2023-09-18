<?php

namespace App\Http\Controllers;

use App\Http\Requests\TableIndex\GetTableIndices;
use App\Services\TableIndex\TableIndexServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableIndexController extends Controller
{
    protected TableIndexServiceInterface $service;

    public function __construct(TableIndexServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getTableIndices(GetTableIndices $request): JsonResponse
    {
        $response = $this->service->getTableIndices($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function tableIndicesOperationPreviews(Request $request): JsonResponse
    {
        $response = $this->service->getTableIndicesOperationPreviews($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function tableIndicesOperationsSaveAndExecute(Request $request): JsonResponse
    {
        $response = $this->service->tableIndicesOperationsSaveAndExecute($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function tableIndicesOperationsSaveWithoutExecuting(Request $request): JsonResponse
    {
        $response = $this->service->tableIndicesOperationsSaveWithoutExecuting($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }
}
