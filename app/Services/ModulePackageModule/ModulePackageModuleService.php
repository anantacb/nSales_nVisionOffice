<?php

namespace App\Services\ModulePackageModule;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\ModulePackageModule\ModulePackageModuleRepositoryInterface;
use Illuminate\Http\Request;

class ModulePackageModuleService implements ModulePackageModuleServiceInterface
{
    protected ModulePackageModuleRepositoryInterface $modulePackageModuleRepository;

    public function __construct(ModulePackageModuleRepositoryInterface $modulePackageModuleRepository)
    {
        $this->modulePackageModuleRepository = $modulePackageModuleRepository;
    }

    public function create(Request $request): ServiceDto
    {
        $this->modulePackageModuleRepository->create([
            'ModulePackageId' => $request->get('ModulePackageId'),
            'ModuleId' => $request->get('ModuleId')
        ]);
        return new ServiceDto("Modules Assigned to ModulePackage Successfully.", 200);
    }

    public function delete(Request $request): ServiceDto
    {
        $this->modulePackageModuleRepository->findByIdAndDelete($request->get('ModulePackageModuleId'));
        return new ServiceDto("Modules Removed from ModulePackage Successfully.", 200);
    }
}
