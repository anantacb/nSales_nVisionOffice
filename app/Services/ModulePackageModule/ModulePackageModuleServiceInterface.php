<?php

namespace App\Services\ModulePackageModule;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface ModulePackageModuleServiceInterface
{
    public function create(Request $request): ServiceDto;

    public function delete(Request $request): ServiceDto;
}
