<?php

namespace App\Services\ModulePackage;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\ModulePackage\ModulePackageRepositoryInterface;
use App\Repositories\Eloquent\Office\ModulePackageModule\ModulePackageModuleRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModulePackageService implements ModulePackageServiceInterface
{
    protected ModulePackageRepositoryInterface $modulePackageRepository;
    protected ModulePackageModuleRepositoryInterface $modulePackageModuleRepository;

    public function __construct(
        ModulePackageRepositoryInterface       $modulePackageRepository,
        ModulePackageModuleRepositoryInterface $modulePackageModuleRepository
    )
    {
        $this->modulePackageRepository = $modulePackageRepository;
        $this->modulePackageModuleRepository = $modulePackageModuleRepository;
    }

    public function getAllModulePackages(Request $request): ServiceDto
    {
        $modulePackages = $this->modulePackageRepository->getByAttributes([], '', ['Id', 'Name'], 'Name');
        return new ServiceDto("ModulePackages retrieved!!!", 200, $modulePackages);
    }

    public function create(Request $request): ServiceDto
    {
        $modulePackage = $this->modulePackageRepository->create([
            'Name' => $request->get('Name'),
            'Type' => $request->get('Type')
        ]);
        return new ServiceDto("ModulePackage Created Successfully.", 200, $modulePackage);
    }

    public function update(Request $request): ServiceDto
    {
        $modulePackage = $this->modulePackageRepository->findByIdAndUpdate(
            $request->get('Id'),
            [
                'Name' => $request->get('Name'),
                'Type' => $request->get('Type')
            ]
        );
        return new ServiceDto("ModulePackage Updated Successfully.", 200, $modulePackage);
    }

    public function getModulePackages(Request $request): ServiceDto
    {
        $request = $request->all();
        $modulePackages = $this->modulePackageRepository->paginatedData($request);
        return new ServiceDto("ModulePackages retrieved!!!", 200, $modulePackages);
    }

    public function delete(Request $request): ServiceDto
    {
        $this->modulePackageModuleRepository->deleteByAttributes(
            [
                ['column' => 'ModulePackageId', 'operand' => '=', 'value' => $request->get('ModulePackageId')]
            ]
        );
        $this->modulePackageRepository->findByIdAndDelete($request->get('ModulePackageId'));
        return new ServiceDto("ModulePackage Deleted Successfully.", 200);
    }

    public function details(Request $request): ServiceDto
    {
        $relations = [
            'modules' => function ($q) {
                $q->withPivot([
                    'Id', 'ModulePackageId', 'ModuleId'
                ])->select([DB::raw('Module.Id as Id'), 'Name']);
            }
        ];
        $modulePackage = $this->modulePackageRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $request->get('ModulePackageId')]
        ], $relations);
        return new ServiceDto("ModulePackage Retrieved Successfully.", 200, $modulePackage);
    }
}
