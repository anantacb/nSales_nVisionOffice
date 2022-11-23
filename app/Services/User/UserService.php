<?php

namespace App\Services\User;


use App\Contracts\ServiceDto;
use Illuminate\Support\Facades\Auth;

class UserService implements UserServiceInterface
{
    public function authUserDetails(): ServiceDto
    {
        return new ServiceDto('Auth User Details Retrieved Successfully', 200, Auth::user());
    }
}
