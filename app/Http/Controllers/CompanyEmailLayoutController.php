<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyEmailLayout\CopyLayoutToCompany;
use App\Http\Requests\CompanyEmailLayout\Create;
use App\Http\Requests\CompanyEmailLayout\DetailsOrDelete;
use App\Http\Requests\CompanyEmailLayout\EmailLayoutOptionsByLanguage;
use App\Http\Requests\CompanyEmailLayout\PreviewTemplate;
use App\Http\Requests\CompanyEmailLayout\Update;
use App\Http\Requests\PaginatedDataRequest;
use App\Services\CompanyEmailLayout\CompanyEmailLayoutServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

class CompanyEmailLayoutController extends Controller
{
    protected CompanyEmailLayoutServiceInterface $service;

    public function __construct(CompanyEmailLayoutServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getEmailLayoutOptionsByLanguage(EmailLayoutOptionsByLanguage $request): JsonResponse
    {
        $response = $this->service->getEmailLayoutOptionsByLanguage($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

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

    public function getDataForPreview(PreviewTemplate $request): JsonResponse
    {
        $response = $this->service->getDataForPreview($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function getPreviewTemplateObject(): JsonResponse
    {
        $response = $this->service->getPreviewTemplateObject();
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function copyLayoutToCompany(CopyLayoutToCompany $request): JsonResponse
    {
        $response = $this->service->copyLayoutToCompany($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

}
