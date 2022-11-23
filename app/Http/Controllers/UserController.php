<?php

namespace App\Http\Controllers;

use App\Services\User\UserServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    protected UserServiceInterface $service;

    public function __construct(UserServiceInterface $service)
    {
        $this->service = $service;
    }

    public function authUserDetails(): JsonResponse
    {
        $response = $this->service->authUserDetails();

        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }
}
