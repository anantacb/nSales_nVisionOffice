<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginatedDataRequest;
use App\Http\Requests\Table\CreateTable;
use App\Http\Requests\Table\DetailsByName;
use App\Http\Requests\Table\DetailsOrDeleteTable;
use App\Http\Requests\Table\UpdateTable;
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
        return ApiResponseTransformer::respond($response);
    }

    public function getDetails(DetailsOrDeleteTable $request): JsonResponse
    {
        $response = $this->service->getDetails($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getDetailsByName(DetailsByName $request): JsonResponse
    {
        $response = $this->service->getDetailsByName($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getCreateTablePreview(CreateTable $request): JsonResponse
    {
        $response = $this->service->getCreateTablePreview($request);
        return ApiResponseTransformer::respond($response);
    }

    public function createTableSaveAndExecute(CreateTable $request): JsonResponse
    {
        $response = $this->service->createTableSaveAndExecute($request);
        return ApiResponseTransformer::respond($response);
    }

    public function createTableSaveWithoutExecuting(CreateTable $request): JsonResponse
    {
        $response = $this->service->createTableSaveWithoutExecuting($request);
        return ApiResponseTransformer::respond($response);
    }

    public function delete(DetailsOrDeleteTable $request): JsonResponse
    {
        $response = $this->service->deleteTable($request);
        return ApiResponseTransformer::respond($response);
    }

    public function update(UpdateTable $request): JsonResponse
    {
        $response = $this->service->updateTable($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getByModule(Request $request): JsonResponse
    {
        $response = $this->service->getByModule($request);
        return ApiResponseTransformer::respond($response);
    }
}
