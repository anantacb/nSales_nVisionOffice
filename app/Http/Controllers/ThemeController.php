<?php

namespace App\Http\Controllers;

use App\Services\Theme\ThemeServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\Request;


class ThemeController extends Controller
{
    protected ThemeServiceInterface $service;

    public function __construct(ThemeServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getThemes()
    {
        $response = $this->service->getThemes();
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function getCompanyTheme(Request $request)
    {
        $response = $this->service->getCompanyTheme($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function triggerBuild($themeId)
    {
        $response = $this->service->triggerBuild($themeId);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }
}
