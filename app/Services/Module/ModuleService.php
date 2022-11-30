<?php

namespace App\Services\Module;


use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\Module\ModuleRepositoryInterface;
use Illuminate\Http\Request;

class ModuleService implements ModuleServiceInterface
{
    protected ModuleRepositoryInterface $moduleRepository;

    public function __construct(ModuleRepositoryInterface $moduleRepository)
    {
        $this->moduleRepository = $moduleRepository;
    }

    public function getAllModules(Request $request): ServiceDto
    {
        $modules = $this->moduleRepository->getByAttributes([], '', ['Id', 'Name'], 'Name');
        return new ServiceDto("Modules retrieved!!!", 200, $modules);
    }
}
