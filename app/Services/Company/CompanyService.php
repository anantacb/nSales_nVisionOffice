<?php

namespace App\Services\Company;

use App\Contracts\ServiceDto;
use App\Helpers\Sql\MysqlQueryGenerator;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyModule\CompanyModuleRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyUser\CompanyUserRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyUserRole\CompanyUserRoleRepositoryInterface;
use App\Repositories\Eloquent\Office\ModulePackage\ModulePackageRepositoryInterface;
use App\Repositories\Eloquent\Office\Role\RoleRepositoryInterface;
use App\Repositories\Eloquent\Office\User\UserRepositoryInterface;
use App\Services\Traits\ModuleHelperTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanyService implements CompanyServiceInterface
{
    use ModuleHelperTrait;

    protected CompanyRepositoryInterface $companyRepository;
    protected ModulePackageRepositoryInterface $modulePackageRepository;
    protected CompanyModuleRepositoryInterface $companyModuleRepository;
    protected RoleRepositoryInterface $roleRepository;
    protected CompanyUserRepositoryInterface $companyUserRepository;
    protected UserRepositoryInterface $userRepository;
    protected CompanyUserRoleRepositoryInterface $companyUserRoleRepository;

    public function __construct(
        CompanyRepositoryInterface         $companyRepository,
        ModulePackageRepositoryInterface   $modulePackageRepository,
        CompanyModuleRepositoryInterface   $companyModuleRepository,
        RoleRepositoryInterface            $roleRepository,
        CompanyUserRepositoryInterface     $companyUserRepository,
        UserRepositoryInterface            $userRepository,
        CompanyUserRoleRepositoryInterface $companyUserRoleRepository
    )
    {
        $this->companyRepository = $companyRepository;
        $this->modulePackageRepository = $modulePackageRepository;
        $this->companyModuleRepository = $companyModuleRepository;
        $this->roleRepository = $roleRepository;
        $this->companyUserRepository = $companyUserRepository;
        $this->userRepository = $userRepository;
        $this->companyUserRoleRepository = $companyUserRoleRepository;
    }

    public function getAllCompanies(Request $request): ServiceDto
    {
        $companies = $this->companyRepository->getByAttributes([], '', ['Id', 'Name', 'CompanyName'], 'Name');
        return new ServiceDto("Companies retrieved!!!", 200, $companies);
    }

    public function getModuleEnabledCompanies(Request $request): ServiceDto
    {
        $companies = $this->companyRepository->getByAttributes([], '', ['Id', 'Name', 'CompanyName'], 'Name', false, [
            [
                "relation" => "modules", "column" => "Module.Id", "operator" => "=", "values" => $request->get("moduleId")
            ]
        ]);
        return new ServiceDto("Companies retrieved!!!", 200, $companies);
    }

    public function create(Request $request): ServiceDto
    {
        $company = $this->companyRepository->create($request->all());

        $sqlQueries = [];
        $sqlQueries[] = MysqlQueryGenerator::getCreateDatabaseSql($company->DatabaseName);

        $relations = [
            'modulePackageModules' => function ($q) {
                $q->with([
                    'module' => function ($q) {
                        $q->with([
                            'tables' => function ($q) {
                                $q->with(['companyTables', 'tableFields.companyTableFields'])
                                    ->whereIn('Type', ['Server', 'Both']);
                            }
                        ]);
                    }
                ]);
            }
        ];

        $modulePackages = $this->modulePackageRepository->firstByAttributes([
            ['column' => 'Name', 'operand' => '=', 'value' => $company->IntegrationType]
        ], $relations);

        foreach ($modulePackages->modulePackageModules as $modulePackageModule) {
            $this->makeEntryInCompanyModuleTable($company->Id, $modulePackageModule->module->Id);
            $modulesTableCreationQueries = $this->getModulesCreateTableSqlQueries($modulePackageModule->module, $company);
            $sqlQueries = array_merge($sqlQueries, $modulesTableCreationQueries);
        }

        foreach ($sqlQueries as $sqlQuery) {
            try {
                DB::statement($sqlQuery);
            } catch (Exception $exception) {
                Log::error("Company Creation. Message: " . $exception->getMessage());
            }
        }

        $defaultRoles = $this->roleRepository->getByAttributes([
            ['column' => 'CompanyId', 'operand' => '=', 'value' => null]
        ]);

        $developerRole = null;

        foreach ($defaultRoles as $role) {
            $newRole = $this->roleRepository->create([
                'CompanyId' => $company->Id,
                'Name' => $role->Name,
                'Type' => $role->Type,
                'Description' => $role->Description
            ]);
            if ($newRole->Type == 'Developer') {
                $developerRole = $newRole;
            }
        }

        $developerUsersIds = $this->companyUserRepository->getByAttributes([], '', '', '', false,
            [
                ["relation" => "roles", "column" => "Type", "operator" => "=", "values" => "Developer"]
            ]
        )->pluck('UserId')->unique()->values()->toArray();

        $developerUsers = $this->userRepository->getByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $developerUsersIds]
        ]);

        foreach ($developerUsers as $key => $developerUser) {
            $companyUser = $this->companyUserRepository->create([
                'CompanyId' => $company->Id,
                'UserId' => $developerUser->Id,
                'Number' => $key + 1,
                'CultureName' => $developerUser->CultureName,
                'Initials' => $developerUser->Initials,
                'Commission' => 0
            ]);

            $this->companyUserRoleRepository->create([
                'RoleId' => $developerRole->Id,
                'CompanyUserId' => $companyUser->Id
            ]);
        }

        return new ServiceDto("Company Created Successfully.", 200, $company);
    }
}
