<?php

namespace App\Http\Controllers;

use App\Services\DocumentApi\DocumentApiServiceInterface;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Http\Request;


class DocumentAPIController extends Controller
{
    protected DocumentApiServiceInterface $service;

    public function __construct(DocumentApiServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getCompanyDocumentApi(Request $request)
    {
        $response = $this->service->getCompanyDocumentApi($request);
        return ApiResponseTransformer::success($response->data, $response->message, $response->statusCode);
    }
}
