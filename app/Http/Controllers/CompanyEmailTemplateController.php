<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyEmailTemplate\CopyTemplateToCompany;
use App\Http\Requests\CompanyEmailTemplate\Create;
use App\Http\Requests\CompanyEmailTemplate\DetailsOrDelete;
use App\Http\Requests\CompanyEmailTemplate\PreviewTemplate;
use App\Http\Requests\CompanyEmailTemplate\Update;
use App\Http\Requests\PaginatedDataRequest;
use App\Services\CompanyEmailTemplate\CompanyEmailTemplateServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyEmailTemplateController extends Controller
{
    protected CompanyEmailTemplateServiceInterface $service;

    public function __construct(CompanyEmailTemplateServiceInterface $service)
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

    public function copyTemplateToCompany(CopyTemplateToCompany $request): JsonResponse
    {
        $response = $this->service->copyTemplateToCompany($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

}
