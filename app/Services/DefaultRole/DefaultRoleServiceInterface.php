<?php

namespace App\Services\DefaultRole;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface DefaultRoleServiceInterface
{
    public function getDefaultRoles(Request $request): ServiceDto;

    public function create(Request $request): ServiceDto;

    public function update(Request $request): ServiceDto;

    public function details(Request $request): ServiceDto;

    public function delete(Request $request): ServiceDto;

    public function syncToCompanyRoles(Request $request): ServiceDto;

    public function syncAllToCompanyRoles(Request $request): ServiceDto;
}
