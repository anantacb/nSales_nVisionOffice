<?php

namespace App\Services\Module;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface ModuleServiceInterface
{
    public function getAllModules(Request $request): ServiceDto;

    public function getActivatedAndAvailableModulesByCompany(Request $request): ServiceDto;
    public function getActivatedModulesByCompany(Request $request): ServiceDto;

    public function activateModule(Request $request): ServiceDto;

    public function getModulesByApplication(Request $request): ServiceDto;

    public function deactivateModule(Request $request): ServiceDto;
}
