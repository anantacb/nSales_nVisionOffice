<?php

namespace App\Services\User;

use App\Contracts\ServiceDto;

interface UserServiceInterface
{
    public function authUserDetails(): ServiceDto;
}
