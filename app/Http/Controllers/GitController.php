<?php

namespace App\Http\Controllers;

use App\Services\Git\GitServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\Request;


class GitController extends Controller
{
    protected GitServiceInterface $service;

    public function __construct(GitServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getCompanyBranches(Request $request)
    {
        try {
            $response = $this->service->getCompanyBranches($request);
            return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
        } catch (\Exception $e) {
            return ApiResponseTransformer::error([], $e->getMessage(), $e->getCode());
        }
    }

    public function createCompanyBranches(Request $request)
    {
        try {
            $response = $this->service->createCompanyBranches($request);
            return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
        } catch (\Exception $e) {
            return ApiResponseTransformer::error([], $e->getMessage(), $e->getCode());
        }
    }
}
