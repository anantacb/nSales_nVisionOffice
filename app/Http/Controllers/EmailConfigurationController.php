<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmailConfiguration\Create;
use App\Services\EmailConfiguration\EmailConfigurationServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

class EmailConfigurationController extends Controller
{
    protected EmailConfigurationServiceInterface $emailConfigurationService;

    public function __construct(EmailConfigurationServiceInterface $emailConfigurationService)
    {
        $this->emailConfigurationService = $emailConfigurationService;
    }

    public function create(Create $request): JsonResponse
    {
        $response = $this->emailConfigurationService->create($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }
}
