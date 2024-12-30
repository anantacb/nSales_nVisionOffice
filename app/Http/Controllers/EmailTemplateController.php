<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmailTemplate\Create;
use App\Http\Requests\EmailTemplate\DetailsOrDelete;
use App\Http\Requests\EmailTemplate\PreviewTemplate;
use App\Http\Requests\EmailTemplate\Update;
use App\Http\Requests\PaginatedDataRequest;
use App\Services\EmailTemplate\EmailTemplateServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    protected EmailTemplateServiceInterface $service;

    public function __construct(EmailTemplateServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getEmailTemplates(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getEmailTemplates($request);
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

    public function getEmailEvents(Request $request): JsonResponse
    {
        $response = $this->service->getEmailEvents($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function getDataForPreview(PreviewTemplate $request): JsonResponse
    {
        $response = $this->service->getDataForPreview($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }


}
