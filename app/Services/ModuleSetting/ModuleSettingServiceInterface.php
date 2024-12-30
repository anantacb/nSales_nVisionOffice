<?php

namespace App\Services\ModuleSetting;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface ModuleSettingServiceInterface
{
    public function getAllModuleSettingsByCompanyId(Request $request): ServiceDto;

    public function getModuleSettings(Request $request): ServiceDto;

    public function getModuleSettingsByName(Request $request): ServiceDto;

    public function getCoreModuleSettingsByName(Request $request): ServiceDto;

    public function getCoreModuleSettings(string $module, array $settingKeys): array;

    public function updateModuleSettingsByCompanyId(Request $request): ServiceDto;

    public function create(Request $request): ServiceDto;

    public function details(Request $request): ServiceDto;

    public function update(Request $request): ServiceDto;

    public function delete(Request $request): ServiceDto;
}
