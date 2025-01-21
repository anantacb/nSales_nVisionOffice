<?php

namespace App\Services\WebShopPage;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface WebShopPageServiceInterface
{
    public function list(Request $request): ServiceDto;

    public function createPages(Request $request): ServiceDto;

    public function createPagesContentForMissingLanguages(Request $request): ServiceDto;
}
