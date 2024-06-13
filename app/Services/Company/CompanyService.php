<?php

namespace App\Services\Company;

use App\Contracts\ServiceDto;
use App\Helpers\Sql\MysqlQueryGenerator;
use App\Models\Office\Company;
use App\Models\Office\Module;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyModule\CompanyModuleRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyUser\CompanyUserRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyUserRole\CompanyUserRoleRepositoryInterface;
use App\Repositories\Eloquent\Office\ImageHostAccount\ImageHostAccountRepositoryInterface;
use App\Repositories\Eloquent\Office\ModulePackage\ModulePackageRepositoryInterface;
use App\Repositories\Eloquent\Office\Role\RoleRepositoryInterface;
use App\Repositories\Eloquent\Office\User\UserRepositoryInterface;
use App\Repositories\Plugin\BunnyCdn\BunnyCdnRepository;
use App\Services\Traits\ModuleHelperTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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

    protected BunnyCdnRepository $bunnyCdnRepository;

    protected ImageHostAccountRepositoryInterface $imageHostAccountRepository;

    public function __construct(
        CompanyRepositoryInterface          $companyRepository,
        ModulePackageRepositoryInterface    $modulePackageRepository,
        CompanyModuleRepositoryInterface    $companyModuleRepository,
        RoleRepositoryInterface             $roleRepository,
        CompanyUserRepositoryInterface      $companyUserRepository,
        UserRepositoryInterface             $userRepository,
        CompanyUserRoleRepositoryInterface  $companyUserRoleRepository,
        BunnyCdnRepository                  $bunnyCdnRepository,
        ImageHostAccountRepositoryInterface $imageHostAccountRepository
    )
    {
        $this->companyRepository = $companyRepository;
        $this->modulePackageRepository = $modulePackageRepository;
        $this->companyModuleRepository = $companyModuleRepository;
        $this->roleRepository = $roleRepository;
        $this->companyUserRepository = $companyUserRepository;
        $this->userRepository = $userRepository;
        $this->companyUserRoleRepository = $companyUserRoleRepository;
        $this->bunnyCdnRepository = $bunnyCdnRepository;
        $this->imageHostAccountRepository = $imageHostAccountRepository;
    }

    /**
     * @param int $company_id
     * @return false|void
     */
    public static function setCompanyDatabaseConnection(int $company_id)
    {
        if (!$company_id) {
            return false;
        }

        $company = Cache::remember(
            'company_' . $company_id,
            Carbon::now()->addHours(24),
            function () use ($company_id) {
                $company_data = Company::with([
                    'imageHostAccount',
                    'modules' => function ($q) use ($company_id) {
                        $q->with(['moduleSettings' => function ($q) use ($company_id) {
                            $q->with(['setting' => function ($q) use ($company_id) {
                                $q->where('CompanyId', $company_id);
                            }]);
                        }]);
                    }
                ])
                    ->find($company_id);

                $formatted_module_settings = [];

                foreach ($company_data->modules as $module) {
                    $formatted_module_settings[$module->Name] = [];
                    foreach ($module->moduleSettings as $module_setting) {
                        if ($module_setting->setting) {
                            $formatted_module_settings[$module->Name][$module_setting->Name] =
                                $module_setting->setting->Value;
                        } else {
                            $formatted_module_settings[$module->Name][$module_setting->Name] =
                                $module_setting->Value;
                        }
                    }
                }

                $default_modules = Module::with([
                    'moduleSettings' => function ($q) use ($company_id) {
                        $q->with(['setting' => function ($q) use ($company_id) {
                            $q->where('CompanyId', $company_id);
                        }]);
                    }])->whereIn('Name', ['System'])->get();

                foreach ($default_modules as $module) {
                    $formatted_module_settings[$module->Name] = [];
                    foreach ($module->moduleSettings as $module_setting) {
                        if ($module_setting->setting) {
                            $formatted_module_settings[$module->Name][$module_setting->Name] =
                                $module_setting->setting->Value;
                        } else {
                            $formatted_module_settings[$module->Name][$module_setting->Name] =
                                $module_setting->Value;
                        }
                    }
                }

                $company_data->module_settings = $formatted_module_settings;

                return $company_data;
            }
        );

        //Session::put('selected_company', $company);


        Config::set('database.connections.mysql_company.database', $company->DatabaseName);

        DB::connection('mysql_company')->reconnect();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function isModuleEnabled(string $moduleName): bool
    {
        $selectedCompany = Cache::get('company_' . request()->get('CompanyId'));
        $modules = $selectedCompany->modules->toArray();
        return in_array($moduleName, array_column($modules, 'Name'));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function getSettingValue(string $moduleName, string $key)
    {
        $selectedCompany = Cache::get('company_' . request()->get('CompanyId'));
        return $selectedCompany->module_settings[$moduleName][$key] ?? null;
    }

    public function getAllCompanies(Request $request): ServiceDto
    {
        $companies = $this->companyRepository->getByAttributes(
            [], [
            /*'modules' => function ($q) {
                $q->select('Module.Id', 'Name', 'Module.ModuleId as ModuleId', 'Type', 'Disabled');
            }*/
        ], ['Id', 'Name', 'CompanyName'], 'Name'
        );
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

    public function getCompanies(Request $request): ServiceDto
    {
        $request = $request->all();
        /*$request['relations'] = [
            ["name" => "module", "columns" => ['Id', 'Name']],
        ];*/
        $companies = $this->companyRepository->paginatedData($request);
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
                                $q->with(['companyTables', 'tableFields.companyTableFields', 'tableIndices.companyTableIndices'])
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

        Storage::disk('sync_ftp')->makeDirectory($company->DomainName);

        $this->setUpImageHosting($company);

        return new ServiceDto("Company Created Successfully.", 200, $company);
    }

    private function setUpImageHosting($company): void
    {
        $baseName = $company->DomainName;
        $attempt = 0;
        $max_attempts = 3;
        while ($attempt < $max_attempts) {
            $attempt++;
            $name = $this->generateAlternativeStorageName($baseName, $attempt);
            $newStorageZone = $this->bunnyCdnRepository->addStorageZone($name);
            if ($newStorageZone["code"] == 400 && isset($newStorageZone['message']) && str_contains(strtolower($newStorageZone['message']), 'name is already taken')) {
                sleep(1);  // Optional: wait before retrying
            } else if ($newStorageZone["code"] == 201) {
                $newPullZone = $this->bunnyCdnRepository->addPullZone($name, $newStorageZone['data']['Id']);
                $this->imageHostAccountRepository->create([
                    'FTPDomainName' => $newStorageZone["data"]["StorageHostname"],
                    'FTPUserName' => $newStorageZone["data"]["Name"],
                    'FTPPassword' => $newStorageZone["data"]["Password"],
                    'Home' => "https://{$newPullZone["data"]["Hostnames"][0]["Value"]}",
                    'CompanyId' => $company->Id,
                    'FTPRootPath' => $newStorageZone["data"]["Name"],
                    'PullZoneId' => $newPullZone["data"]["Id"],
                    'StorageZoneId' => $newStorageZone["data"]["Id"],
                    "UserName" => $newStorageZone["data"]["Name"],
                    "UserEmail" => "mly@nsales.dk",
                ]);
                $attempt = $max_attempts;
            }
        }
    }

    private function generateAlternativeStorageName($base_name, $attempt): string
    {
        if ($attempt !== 1) {
            // Generate a random suffix to append to the base name
            $suffix = substr(md5(uniqid(rand(), true)), 0, 3);
            return "$base_name$attempt$suffix";
        }
        return $base_name;
    }

    public function update(Request $request): ServiceDto
    {
        $relations = [
            'modules' => function ($q) {
                $q->with([
                    'tables' => function ($q) {
                        $q->with(['companyTables'])
                            ->whereIn('Type', ['Server', 'Both']);
                    }
                ]);
            }
        ];

        $initialCompany = $this->companyRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $request->get('Id')]
        ], $relations);

        $updatedCompany = $this->companyRepository->findByIdAndUpdate(
            $request->get('Id'),
            $request->except('Id')
        );

        /**
         * Database Name Updated as Company Name and Domain updated
         * So Rename the database
         */
        if ($initialCompany->DatabaseName !== $updatedCompany->DatabaseName) {
            $sqlQueries = [];
            $sqlQueries[] = MysqlQueryGenerator::getCreateDatabaseSql($updatedCompany->DatabaseName);
            $sqlQueries[] = "SET SQL_MODE='ALLOW_INVALID_DATES';";
            foreach ($initialCompany->modules as $module) {
                foreach ($module->tables as $table) {
                    if ($table->companyTables->count()) {
                        $companyTableCompanyIds = $table->companyTables->pluck('CompanyId')->toArray();
                        if (in_array($initialCompany->Id, $companyTableCompanyIds)) {
                            $sqlQueries[] = MysqlQueryGenerator::getCopyTableStructureSql(
                                $initialCompany->DatabaseName,
                                $table->Name,
                                $updatedCompany->DatabaseName,
                                $table->Name
                            );
                            $sqlQueries[] = MysqlQueryGenerator::getCopyTableDataSql(
                                $initialCompany->DatabaseName,
                                $table->Name,
                                $updatedCompany->DatabaseName,
                                $table->Name
                            );
                        }
                    } else {
                        $sqlQueries[] = MysqlQueryGenerator::getCopyTableStructureSql(
                            $initialCompany->DatabaseName,
                            $table->Name,
                            $updatedCompany->DatabaseName,
                            $table->Name
                        );
                        $sqlQueries[] = MysqlQueryGenerator::getCopyTableDataSql(
                            $initialCompany->DatabaseName,
                            $table->Name,
                            $updatedCompany->DatabaseName,
                            $table->Name
                        );
                    }
                }
            }
            $sqlQueries[] = MysqlQueryGenerator::getDropDatabaseSql($initialCompany->DatabaseName);

            foreach ($sqlQueries as $sqlQuery) {
                try {
                    DB::statement($sqlQuery);
                } catch (Exception $exception) {
                    Log::error("Update Company Rename Database Error. Message: {$exception->getMessage()}");
                }
            }
        }
        return new ServiceDto("Company Updated Successfully.", 200, $updatedCompany);
    }

    public function details(Request $request): ServiceDto
    {
        $company = $this->companyRepository->findById($request->get('CompanyId'));
        return new ServiceDto("Company Retrieved Successfully.", 200, $company);
    }

    public function delete(Request $request): ServiceDto
    {
        $company = $this->companyRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $request->get('CompanyId')]
        ], ["imageHostAccount"]);
        $sqlQuery = MysqlQueryGenerator::getDropDatabaseSql($company->DatabaseName);
        try {
            DB::statement($sqlQuery);
        } catch (Exception $exception) {
            Log::error("Delete Company Database Error. Message: {$exception->getMessage()}");
        }
        if ($company->imageHostAccount) {
            $this->bunnyCdnRepository->deleteStorageZone($company->imageHostAccount->StorageZoneId);
        }
        $this->companyRepository->findByIdAndDelete($company->Id);
        return new ServiceDto("Company Deleted Successfully.", 200, []);
    }

    public function getAssignableCompaniesByUser(Request $request): ServiceDto
    {
        $relations = [
            'companyUsers'
        ];
        $user = $this->userRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $request->get('UserId')]
        ], $relations);

        $assignedCompanyIds = $user->companyUsers->pluck('CompanyId')->toArray();

        $assignAbleCompanies = $this->companyRepository->getByAttributes([
            ['column' => 'Id', 'operand' => '!=', 'value' => $assignedCompanyIds]
        ], '', ['Id', 'Name', 'CompanyName']);

        return new ServiceDto("Companies Retrieved Successfully.", 200, $assignAbleCompanies);
    }
}
