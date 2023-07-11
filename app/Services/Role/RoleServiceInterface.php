<?php

namespace App\Services\Role;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface RoleServiceInterface
{
    public function getAssignableRolesByCompany(Request $request): ServiceDto;

    public function getCompanyRoles(Request $request): ServiceDto;

    public function create(Request $request): ServiceDto;

    public function update(Request $request): ServiceDto;

    public function delete(Request $request): ServiceDto;

    public function details(Request $request): ServiceDto;
}
