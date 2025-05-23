<?php

namespace App\Services\WebShopLanguage;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface WebShopLanguageServiceInterface
{
    public function getAllWebShopLanguages(Request $request): ServiceDto;
}
