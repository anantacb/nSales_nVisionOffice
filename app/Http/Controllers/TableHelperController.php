<?php

namespace App\Http\Controllers;

use App\Http\Requests\TableHelper\GetAllTableColumns;
use App\Http\Requests\TableHelper\GetColumnDistinctValues;
use App\Http\Requests\TableHelper\GetEnumValues;
use App\Services\TableHelper\TableHelperServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

class TableHelperController extends Controller
{
    protected TableHelperServiceInterface $tableHelperService;

    public function __construct(TableHelperServiceInterface $tableHelperService)
    {
        $this->tableHelperService = $tableHelperService;
    }

    public function getColumnDistinctValues(GetColumnDistinctValues $request): JsonResponse
    {
        $response = $this->tableHelperService->getColumnDistinctValues($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function getEnumValues(GetEnumValues $request): JsonResponse
    {
        $response = $this->tableHelperService->getEnumValues($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function getAllTableColumnNames(GetAllTableColumns $request): JsonResponse
    {
        $response = $this->tableHelperService->getAllTableColumnNames($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }
}
