<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmailLayout\Create;
use App\Http\Requests\EmailLayout\DetailsOrDelete;
use App\Http\Requests\EmailLayout\Update;
use App\Http\Requests\PaginatedDataRequest;
use App\Services\EmailLayout\EmailLayoutServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailLayoutController extends Controller
{
    protected EmailLayoutServiceInterface $service;

    public function __construct(EmailLayoutServiceInterface $service)
    {
        $this->service = $service;
    }

//    public function getAllLanguages(Request $request): JsonResponse
//    {
//        $response = $this->service->getAllLanguages($request);
//        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
//    }

    public function getEmailLayouts(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getEmailLayouts($request);
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

    public function details(DetailsOrDelete $request): JsonResponse
    {
        $response = $this->service->details($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function delete(DetailsOrDelete $request): JsonResponse
    {
        $response = $this->service->delete($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function getDataForPreview(Request $request): JsonResponse
    {
        $response = $this->service->getDataForPreview($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

}
