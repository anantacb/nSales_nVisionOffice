<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginatedDataRequest;
use App\Services\Table\TableServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

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
}
