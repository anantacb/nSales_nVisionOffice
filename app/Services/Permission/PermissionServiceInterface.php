<?php

namespace App\Services\Permission;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface PermissionServiceInterface
{
    public function listAll(Request $request): ServiceDto;

    public function getRolePermissions(Request $request): ServiceDto;

    public function syncRolePermissions(Request $request): ServiceDto;

    public function getPermissions(Request $request): ServiceDto;

    public function create(Request $request): ServiceDto;

    public function update(Request $request): ServiceDto;

    public function details(Request $request): ServiceDto;

    public function delete(Request $request): ServiceDto;
}
