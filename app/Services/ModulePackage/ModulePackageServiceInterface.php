<?php

namespace App\Services\ModulePackage;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface ModulePackageServiceInterface
{
    public function getAllModulePackages(Request $request): ServiceDto;

    public function getModulePackages(Request $request): ServiceDto;

    public function create(Request $request): ServiceDto;

    public function details(Request $request): ServiceDto;

    public function update(Request $request): ServiceDto;

    public function delete(Request $request): ServiceDto;
}
