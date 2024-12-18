<?php

namespace App\Services\DocumentApi;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface DocumentApiServiceInterface
{
    public function getCompanyDocumentApi(Request $request): ServiceDto;
}
