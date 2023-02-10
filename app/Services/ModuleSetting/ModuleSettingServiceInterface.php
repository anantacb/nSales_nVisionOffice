<?php

namespace App\Services\ModuleSetting;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface ModuleSettingServiceInterface
{
    public function getAllModuleSettingsByCompanyId(Request $request): ServiceDto;

    public function updateModuleSettingsByCompanyId(Request $request): ServiceDto;
}
