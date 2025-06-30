<?php

namespace App\Http\Controllers;

use App\Services\WebShopLanguage\WebShopLanguageServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebShopLanguageController extends Controller
{
    protected WebShopLanguageServiceInterface $service;

    public function __construct(WebShopLanguageServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getAllWebShopLanguages(Request $request): JsonResponse
    {
        $response = $this->service->getAllWebShopLanguages($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

}
