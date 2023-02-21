<?php

namespace App\Services\User;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface UserServiceInterface
{
    public function authUserDetails(): ServiceDto;

    public function createCompanyUser(Request $request): ServiceDto;
}
