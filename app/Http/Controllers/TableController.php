<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginatedDataRequest;
use App\Http\Requests\Table\CreateTable;
use App\Http\Requests\Table\DetailsOrDeleteTable;
use App\Services\Table\TableServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableController extends Controller
{
    protected TableServiceInterface $service;

    public function __construct(TableServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getTables(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getTables($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function getDetails(DetailsOrDeleteTable $request): JsonResponse
    {
        $response = $this->service->getDetails($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function getCreateTablePreview(CreateTable $request): JsonResponse
    {
        $response = $this->service->getCreateTablePreview($request);
        if ($response->statusCode == 200) {
            return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
        } else {
            return ApiResponseTransformer::error($response->data, $response->message, $response->statusCode);
        }
    }

    public function createTableSaveAndExecute(CreateTable $request): JsonResponse
    {
        $response = $this->service->createTableSaveAndExecute($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function createTableSaveWithoutExecuting(CreateTable $request): JsonResponse
    {
        $response = $this->service->createTableSaveWithoutExecuting($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function delete(DetailsOrDeleteTable $request): JsonResponse
    {
        $response = $this->service->deleteTable($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function getByModule(Request $request): JsonResponse
    {
        $response = $this->service->getByModule($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }
}
