<?php

namespace App\Http\Controllers;

use App\Services\WebShopPage\WebShopPageServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\Request;

class WebShopPageController extends Controller
{
    protected WebShopPageServiceInterface $service;

    public function __construct(WebShopPageServiceInterface $service)
    {
        $this->service = $service;
    }

    public function list(Request $request)
    {
        $response = $this->service->list($request);
        return ApiResponseTransformer::respond($response);
    }

    public function createPages(Request $request)
    {
        $response = $this->service->createPages($request);
        return ApiResponseTransformer::respond($response);
    }

    public function createPagesContentForMissingLanguages(Request $request)
    {
        $response = $this->service->createPagesContentForMissingLanguages($request);
        return ApiResponseTransformer::respond($response);
    }
}
