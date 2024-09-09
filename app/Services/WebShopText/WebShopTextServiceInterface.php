<?php

namespace App\Services\WebShopText;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface WebShopTextServiceInterface
{
    public function getByItem(Request $request): ServiceDto;
}
