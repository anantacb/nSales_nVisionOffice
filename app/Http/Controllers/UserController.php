<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\CreateCompanyUser;
use App\Services\User\UserServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserServiceInterface $service;

    public function __construct(UserServiceInterface $service)
    {
        $this->service = $service;
    }

    public function createCompanyUser(CreateCompanyUser $request): JsonResponse
    {
        $response = $this->service->createCompanyUser($request);

        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function getCompanyUsers(Request $request): JsonResponse
    {
        $response = $this->service->getCompanyUsers($request);

        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }

    public function authUserDetails(): JsonResponse
    {
        $response = $this->service->authUserDetails();
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }
}
