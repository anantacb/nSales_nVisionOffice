<?php

namespace App\Http\Controllers;

use App\Services\Onboard\OnboardServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\Request;


class OnboardController extends Controller
{
    protected OnboardServiceInterface $service;

    public function __construct(OnboardServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getCompanyOnboardStatus(Request $request)
    {
        $response = $this->service->getCompanyOnboardStatus($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function updateCompanyOnboardStatus(Request $request)
    {
        $response = $this->service->updateCompanyOnboardStatus($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }
}
