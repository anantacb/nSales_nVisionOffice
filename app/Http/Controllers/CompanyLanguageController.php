<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyLanguage\AddCompanyLanguage;
use App\Http\Requests\CompanyLanguage\DetailsOrDelete;
use App\Http\Requests\PaginatedDataRequest;
use App\Services\CompanyLanguage\CompanyLanguageServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyLanguageController extends Controller
{
    protected CompanyLanguageServiceInterface $service;

    public function __construct(CompanyLanguageServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getAllCompanyLanguages(Request $request): JsonResponse
    {
        $response = $this->service->getAllCompanyLanguages($request);
        return ApiResponseTransformer::respond($response);
    }

    public function getCompanyLanguages(PaginatedDataRequest $request): JsonResponse
    {
        $response = $this->service->getCompanyLanguages($request);
        return ApiResponseTransformer::respond($response);
    }

    public function delete(DetailsOrDelete $request): JsonResponse
    {
        $response = $this->service->delete($request);
        return ApiResponseTransformer::respond($response);
    }

    public function addCompanyLanguage(AddCompanyLanguage $request): JsonResponse
    {
        $response = $this->service->addCompanyLanguage($request);
        return ApiResponseTransformer::respond($response);
    }

    public function setAsDefaultLanguage(DetailsOrDelete $request): JsonResponse
    {
        $response = $this->service->setAsDefaultLanguage($request);
        return ApiResponseTransformer::respond($response);
    }
}
