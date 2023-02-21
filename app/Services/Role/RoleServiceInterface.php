<?php

namespace App\Services\Role;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface RoleServiceInterface
{
    public function getAssignableRolesByCompany(Request $request): ServiceDto;
}
