<?php

namespace App\Services\Module;


use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyModule\CompanyModuleRepositoryInterface;
use App\Repositories\Eloquent\Office\Module\ModuleRepositoryInterface;
use App\Services\Traits\ModuleHelperTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ModuleService implements ModuleServiceInterface
{
    use ModuleHelperTrait;

    protected ModuleRepositoryInterface $moduleRepository;
    protected CompanyModuleRepositoryInterface $companyModuleRepository;

    protected CompanyRepositoryInterface $companyRepository;

    public function __construct(
        ModuleRepositoryInterface        $moduleRepository,
        CompanyModuleRepositoryInterface $companyModuleRepository,
        CompanyRepositoryInterface       $companyRepository
    )
    {
        $this->moduleRepository = $moduleRepository;
        $this->companyModuleRepository = $companyModuleRepository;
        $this->companyRepository = $companyRepository;
    }

    public function getAllModules(Request $request): ServiceDto
    {
        $modules = $this->moduleRepository->getByAttributes([], '', ['Id', 'Name'], 'Name');
        return new ServiceDto("Modules retrieved!!!", 200, $modules);
    }

    public function getActivatedAndAvailableModulesByCompany(Request $request): ServiceDto
    {
        $companyId = $request->get('company_id');
        $companyModules = $this->companyModuleRepository->getByAttributes([
                ['column' => 'CompanyId', 'operand' => '=', 'value' => $companyId]
            ]
        );

        $relations = [
            'subModules' => function ($q) {
                $q->with([
                    'tables' => function ($q) {
                        $q->whereIn('Type', ['Server', 'Both']);
                    }
                ]);
            },
            'tables' => function ($q) use ($companyId) {
                $q->with(['companyTables' => function ($q) use ($companyId) {
                    $q->where('CompanyId', $companyId);
                }])->whereIn('Type', ['Server', 'Both']);
            }];

        $installedModuleIds = $companyModules->pluck('ModuleId')->toArray();
        $installedModules = $this->moduleRepository->getByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $installedModuleIds]
        ], $relations, '', 'Name');

        $formattedInstalledModules = [];
        foreach ($installedModules as $installedModule) {
            $formattedInstalledModules[] = $this->formatModule($installedModule);
        }

        $excludingModuleTypes = ['Root', 'Core', 'Package'];
        $excludingModules = $this->moduleRepository->getByAttributes([
            ['column' => 'Type', 'operand' => '=', 'value' => $excludingModuleTypes]
        ]);

        $defaultModuleIds = $excludingModules->pluck('Id')->toArray();

        $availableModules = $this->moduleRepository->getByAttributes([
            ['column' => 'Id', 'operand' => '!=', 'value' => array_merge($installedModuleIds, $defaultModuleIds)],
        ], $relations, '', 'Name');

        $formattedAvailableModules = [];

        foreach ($availableModules as $availableModule) {
            $formattedAvailableModules[] = $this->formatModule($availableModule);
        }

        $data = [
            'installedModules' => $formattedInstalledModules,
            'availableModules' => $formattedAvailableModules
        ];

        return new ServiceDto("Modules retrieved!!!", 200, $data);
    }

    private function formatModule($module): array
    {
        $hasSubModuleTables = false;
        if ($module->subModules->count()) {
            foreach ($module->subModules as $subModule) {
                if ($subModule->tables->count()) {
                    $hasSubModuleTables = true;
                    break;
                }
            }
        }

        return [
            'Id' => $module->Id,
            'Name' => $module->Name,
            'HasSubModules' => (boolean)$module->subModules->count(),
            'HasTables' => (boolean)$module->tables->count(),
            'SubModuleIds' => $module->subModules->pluck('Id')->toArray(),
            'SubModuleNames' => $module->subModules->pluck('Name')->toArray(),
            'TableIds' => $module->tables->pluck('Id')->toArray(),
            'TableNames' => $module->tables->pluck('Name')->toArray(),
            'HasSubModuleTables' => $hasSubModuleTables
        ];
    }

    public function activateModule(Request $request): ServiceDto
    {
        $requestModule = $request->get('module');
        $companyId = $request->get('company_id');
        $company = $this->companyRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $companyId]
        ]);

        $relations = [
            'subModules' => function ($q) {
                $q->with(['tables' => function ($q) {
                    $q->with(['companyTables', 'tableFields.companyTableFields'])
                        ->whereIn('Type', ['Server', 'Both']);
                }]);
            },
            'tables' => function ($q) {
                $q->with(['companyTables', 'tableFields.companyTableFields'])
                    ->whereIn('Type', ['Server', 'Both']);
            }];

        $module = $this->moduleRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $requestModule['Id']]
        ], $relations);

        /**
         * Install Sub Module
         *      -> Create tables
         *          -> Entry In CompanyModule table
         *          -> Retrieve all tables for module and submodules
         *      -> Don't Create tables
         *          -> Entry In CompanyModule table
         * Don't Install Sub Module
         *      -> Create tables
         *          -> Entry In CompanyModule table
         *          -> Retrieve all tables for module
         *      -> Don't Create tables
         *          -> Entry In CompanyModule table
         */

        $sqlQueries = [];

        // Has Sub Modules And Install
        if ($requestModule['InstallSubModules']) {
            // Entry In CompanyModule table TODO
            $subModuleIds = $module->subModules->pluck('Id')->toArray();
            $moduleAndSubModuleIds = array_merge([$module->Id], $subModuleIds);
            foreach ($moduleAndSubModuleIds as $moduleId) {
                $this->makeEntryInCompanyModuleTable($companyId, $moduleId);
            }

            if ($requestModule['CreateTables']) {
                // Retrieve all tables of module and submodule
                $sqlQueries = $this->getModulesCreateTableSqlQueries($module, $company);

                foreach ($module->subModules as $subModule) {
                    $subModulesTableCreationQueries = $this->getModulesCreateTableSqlQueries($subModule, $company);
                    $sqlQueries = array_merge($sqlQueries, $subModulesTableCreationQueries);
                }
            }
        } else {
            // Entry In CompanyModule table
            $this->makeEntryInCompanyModuleTable($companyId, $requestModule['Id']);

            if ($requestModule['CreateTables']) {
                $sqlQueries = $this->getModulesCreateTableSqlQueries($module, $company);
            }
        }

        foreach ($sqlQueries as $sqlQuery) {
            try {
                DB::statement($sqlQuery);
            } catch (Exception $exception) {
                Log::error("Module Activation. Message: " . $exception->getMessage());
            }
        }

        return new ServiceDto("Module installed Successfully!!!", 200, []);
    }


    public function deactivateModule(Request $request): ServiceDto
    {
        $requestModule = $request->get('module');
        $companyId = $request->get('company_id');
        $company = $this->companyRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $companyId]
        ]);

        $relations = [
            'subModules' => function ($q) {
                $q->with(['tables' => function ($q) {
                    $q->with(['companyTables'])
                        ->whereIn('Type', ['Server', 'Both']);
                }]);
            },
            'tables' => function ($q) {
                $q->with(['companyTables'])
                    ->whereIn('Type', ['Server', 'Both']);
            }];

        $module = $this->moduleRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $requestModule['Id']]
        ], $relations);

        /**
         * UnInstall Sub Module
         *      -> Delete tables
         *          -> Delete Entry In CompanyModule table
         *          -> Retrieve all tables for module and submodules and Delete
         *      -> Don't Delete tables
         *          -> Delete Entry In CompanyModule table
         * Don't UnInstall Sub Module
         *      -> Delete tables
         *          -> Delete Entry In CompanyModule table
         *          -> Retrieve all tables for module and Delete
         *      -> Don't Delete tables
         *          -> Delete Entry In CompanyModule table
         */

        $sqlQueries = [];

        // Has Sub Modules And Install
        if ($requestModule['UnInstallSubModules']) {
            // Delete Entry From CompanyModule table
            $subModuleIds = $module->subModules->pluck('Id')->toArray();
            $moduleAndSubModuleIds = array_merge([$module->Id], $subModuleIds);
            foreach ($moduleAndSubModuleIds as $moduleId) {
                $this->deleteEntryFromCompanyModuleTable($companyId, $moduleId);
            }

            if ($requestModule['DeleteTables']) {
                // Retrieve all tables of module and submodule
                $sqlQueries = $this->getModulesDeleteTableSqlQueries($module, $company);

                foreach ($module->subModules as $subModule) {
                    $subModulesTableDeleteQueries = $this->getModulesDeleteTableSqlQueries($subModule, $company);
                    $sqlQueries = array_merge($sqlQueries, $subModulesTableDeleteQueries);
                }
            }
        } else {
            // Delete Entry From CompanyModule table
            $this->deleteEntryFromCompanyModuleTable($companyId, $requestModule['Id']);

            if ($requestModule['DeleteTables']) {
                $sqlQueries = $this->getModulesDeleteTableSqlQueries($module, $company);
            }
        }

        foreach ($sqlQueries as $sqlQuery) {
            try {
                DB::statement($sqlQuery);
            } catch (Exception $exception) {
                Log::error("Module Deactivation. Message: " . $exception->getMessage());
            }
        }

        return new ServiceDto("Module uninstalled Successfully!!!", 200, []);
    }
}
