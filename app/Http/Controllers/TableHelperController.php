<?php

namespace App\Http\Controllers;

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

    public function getColumnDistinctValues()
    {

    }

    public function getEnumValues(GetEnumValues $request): JsonResponse
    {
        $response = $this->tableHelperService->getEnumValues($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }
}
