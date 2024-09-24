<?php

namespace App\Http\Controllers;

use App\Http\Requests\Database\CopyDB;
use App\Services\Database\DatabaseServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DatabaseController extends Controller
{
    protected DatabaseServiceInterface $service;

    public function __construct(DatabaseServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getAllCompanies(Request $request): JsonResponse
    {
        $response = $this->service->getAllCompanies($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }
    
    public function copyDBtoDev(CopyDB $request): JsonResponse
    {
        $response = $this->service->copyDBtoDevServer($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

}
