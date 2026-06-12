<?php

namespace App\Http\Controllers;

use App\Http\Requests\TableField\GetCompanyAllTableFields;
use App\Http\Requests\TableField\GetCompanySpecificTableFields;
use App\Http\Requests\TableField\GetGeneralTableFields;
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
        return ApiResponseTransformer::respond($response);
    }

    public function getGeneralTableFields(GetGeneralTableFields $request): JsonResponse
    {
        $response = $this->service->getGeneralTableFields($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getCompanySpecificTableFields(GetCompanySpecificTableFields $request): JsonResponse
    {
        $response = $this->service->getCompanySpecificTableFields($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getCompanyAllTableFields(GetCompanyAllTableFields $request): JsonResponse
    {
        $response = $this->service->getCompanyAllTableFields($request);
        return ApiResponseTransformer::respond($response);
    }

    public function tableFieldsOperationPreviews(Request $request): JsonResponse
    {
        $response = $this->service->getTableFieldsOperationPreviews($request);
        return ApiResponseTransformer::respond($response);
    }

    public function tableFieldsOperationsSaveAndExecute(Request $request): JsonResponse
    {
        $response = $this->service->tableFieldsOperationsSaveAndExecute($request);
        return ApiResponseTransformer::respond($response);
    }

    public function tableFieldsOperationsSaveWithoutExecuting(Request $request): JsonResponse
    {
        $response = $this->service->tableFieldsOperationsSaveWithoutExecuting($request);
        return ApiResponseTransformer::respond($response);
    }
}
