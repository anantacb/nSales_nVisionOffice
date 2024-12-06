<?php

namespace App\Services\WebShopUser;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface WebShopUserServiceInterface
{
    public function details(Request $request): ServiceDto;

    public function createTestUser(Request $request): ServiceDto;
}
