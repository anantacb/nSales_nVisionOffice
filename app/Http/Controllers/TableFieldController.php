<?php

namespace App\Http\Controllers;

use App\Http\Requests\TableField\GetTableFields;
use App\Services\TableField\TableFieldServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableFieldController extends Controller
{
    protected TableFieldServiceInterface $service;

    public function __construct(TableFieldServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getTableFields(GetTableFields $request): JsonResponse
    {
        $response = $this->service->getTableFields($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function tableFieldsOperationPreviews(Request $request): JsonResponse
    {
        $response = $this->service->getTableFieldsOperationPreviews($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function tableFieldsOperationsSaveAndExecute(Request $request): JsonResponse
    {
        $response = $this->service->tableFieldsOperationsSaveAndExecute($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function tableFieldsOperationsSaveWithoutExecuting(Request $request): JsonResponse
    {
        $response = $this->service->tableFieldsOperationsSaveWithoutExecuting($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }
}
