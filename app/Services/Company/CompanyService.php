<?php

namespace App\Services\Company;

use App\Contracts\ServiceDto;
use App\Helpers\Sql\MysqlQueryGenerator;
use App\Models\Office\Company;
use App\Models\Office\Module;
use App\Repositories\Eloquent\Admin\FtpUser\FtpUserRepositoryInterface;
use App\Repositories\Eloquent\Company\CompanyEmailLayout\CompanyEmailLayoutRepositoryInterface;
use App\Repositories\Eloquent\Company\CompanyEmailTemplate\CompanyEmailTemplateRepositoryInterface;
use App\Repositories\Eloquent\Company\CompanyLanguage\CompanyLanguageRepositoryInterface;
use App\Repositories\Eloquent\Company\CompanyTranslation\CompanyTranslationRepositoryInterface;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyModule\CompanyModuleRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyTable\CompanyTableRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyTableField\CompanyTableFieldRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyTableIndex\CompanyTableIndexRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyTheme\CompanyThemeRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyUser\CompanyUserRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyUserRole\CompanyUserRoleRepositoryInterface;
use App\Repositories\Eloquent\Office\DataFilter\DataFilterRepositoryInterface;
use App\Repositories\Eloquent\Office\EmailConfiguration\EmailConfigurationRepositoryInterface;
use App\Repositories\Eloquent\Office\EmailLayout\EmailLayoutRepositoryInterface;
use App\Repositories\Eloquent\Office\EmailTemplate\EmailTemplateRepositoryInterface;
use App\Repositories\Eloquent\Office\ImageHostAccount\ImageHostAccountRepositoryInterface;
use App\Repositories\Eloquent\Office\Language\LanguageRepositoryInterface;
use App\Repositories\Eloquent\Office\Module\ModuleRepositoryInterface;
use App\Repositories\Eloquent\Office\ModulePackage\ModulePackageRepositoryInterface;
use App\Repositories\Eloquent\Office\ModuleSetting\ModuleSettingRepositoryInterface;
use App\Repositories\Eloquent\Office\PostmarkEmailServer\PostmarkEmailServerRepositoryInterface;
use App\Repositories\Eloquent\Office\Role\RoleRepositoryInterface;
use App\Repositories\Eloquent\Office\Setting\SettingRepositoryInterface;
use App\Repositories\Eloquent\Office\Translation\TranslationRepositoryInterface;
use App\Repositories\Eloquent\Office\User\UserRepositoryInterface;
use App\Repositories\Eloquent\Office\UserInvitation\UserInvitationRepositoryInterface;
use App\Repositories\Plugin\BunnyCdn\BunnyCdnRepository;
use App\Repositories\Plugin\NsalesAdminDjangoApi\NsalesAdminDjangoApiRepository;
use App\Repositories\Plugin\Postmark\PostmarkRepository;
use App\Services\CompanyLanguage\CompanyLanguageServiceInterface;
use App\Services\Traits\ModuleHelperTrait;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    protected PostmarkRepository $postmarkRepository;
    protected PostmarkEmailServerRepositoryInterface $postmarkEmailServerRepository;

    protected ModuleSettingRepositoryInterface $moduleSettingRepository;

    protected ModuleRepositoryInterface $moduleRepository;

    protected SettingRepositoryInterface $settingRepository;

    protected UserInvitationRepositoryInterface $userInvitationRepository;

    protected EmailConfigurationRepositoryInterface $emailConfigurationRepository;

    protected FtpUserRepositoryInterface $ftpUserRepository;

    protected LanguageRepositoryInterface $languageRepository;
    protected TranslationRepositoryInterface $translationRepository;
    protected CompanyLanguageRepositoryInterface $companyLanguageRepository;
    protected CompanyTranslationRepositoryInterface $companyTranslationRepository;

    protected CompanyTableFieldRepositoryInterface $companyTableFieldRepository;
    protected CompanyTableIndexRepositoryInterface $companyTableIndexRepository;
    protected CompanyTableRepositoryInterface $companyTableRepository;
    protected CompanyThemeRepositoryInterface $companyThemeRepository;

    protected DataFilterRepositoryInterface $dataFilterRepository;

    protected NsalesAdminDjangoApiRepository $nsalesAdminDjangoApiRepository;

    protected EmailLayoutRepositoryInterface $emailLayoutRepository;
    protected EmailTemplateRepositoryInterface $emailTemplateRepository;

    protected CompanyEmailLayoutRepositoryInterface $companyEmailLayoutRepository;

    protected CompanyEmailTemplateRepositoryInterface $companyEmailTemplateRepository;

    protected CompanyLanguageServiceInterface $companyLanguageService;

    public function __construct(
        CompanyRepositoryInterface              $companyRepository,
        ModulePackageRepositoryInterface        $modulePackageRepository,
        CompanyModuleRepositoryInterface        $companyModuleRepository,
        RoleRepositoryInterface                 $roleRepository,
        CompanyUserRepositoryInterface          $companyUserRepository,
        UserRepositoryInterface                 $userRepository,
        CompanyUserRoleRepositoryInterface      $companyUserRoleRepository,
        BunnyCdnRepository                      $bunnyCdnRepository,
        ImageHostAccountRepositoryInterface     $imageHostAccountRepository,
        PostmarkRepository                      $postmarkRepository,
        PostmarkEmailServerRepositoryInterface  $postmarkEmailServerRepository,
        ModuleSettingRepositoryInterface        $moduleSettingRepository,
        SettingRepositoryInterface              $settingRepository,
        ModuleRepositoryInterface               $moduleRepository,
        UserInvitationRepositoryInterface       $userInvitationRepository,
        EmailConfigurationRepositoryInterface   $emailConfigurationRepository,
        FtpUserRepositoryInterface              $ftpUserRepository,
        LanguageRepositoryInterface             $languageRepository,
        TranslationRepositoryInterface          $translationRepository,
        CompanyLanguageRepositoryInterface      $companyLanguageRepository,
        CompanyTranslationRepositoryInterface   $companyTranslationRepository,
        CompanyTableFieldRepositoryInterface    $companyTableFieldRepository,
        CompanyTableIndexRepositoryInterface    $companyTableIndexRepository,
        CompanyTableRepositoryInterface         $companyTableRepository,
        CompanyThemeRepositoryInterface         $companyThemeRepository,
        DataFilterRepositoryInterface           $dataFilterRepository,
        NsalesAdminDjangoApiRepository          $nsalesAdminDjangoApiRepository,
        EmailLayoutRepositoryInterface          $emailLayoutRepository,
        EmailTemplateRepositoryInterface        $emailTemplateRepository,
        CompanyEmailLayoutRepositoryInterface   $companyEmailLayoutRepository,
        CompanyEmailTemplateRepositoryInterface $companyEmailTemplateRepository,
        CompanyLanguageServiceInterface         $companyLanguageService
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
        $this->postmarkRepository = $postmarkRepository;
        $this->postmarkEmailServerRepository = $postmarkEmailServerRepository;
        $this->moduleSettingRepository = $moduleSettingRepository;
        $this->settingRepository = $settingRepository;
        $this->moduleRepository = $moduleRepository;
        $this->userInvitationRepository = $userInvitationRepository;
        $this->emailConfigurationRepository = $emailConfigurationRepository;
        $this->ftpUserRepository = $ftpUserRepository;
        $this->languageRepository = $languageRepository;
        $this->translationRepository = $translationRepository;
        $this->companyLanguageRepository = $companyLanguageRepository;
        $this->companyTranslationRepository = $companyTranslationRepository;
        $this->companyTableFieldRepository = $companyTableFieldRepository;
        $this->companyTableIndexRepository = $companyTableIndexRepository;
        $this->companyTableRepository = $companyTableRepository;
        $this->companyThemeRepository = $companyThemeRepository;
        $this->dataFilterRepository = $dataFilterRepository;
        $this->nsalesAdminDjangoApiRepository = $nsalesAdminDjangoApiRepository;
        $this->emailLayoutRepository = $emailLayoutRepository;
        $this->emailTemplateRepository = $emailTemplateRepository;
        $this->companyEmailLayoutRepository = $companyEmailLayoutRepository;
        $this->companyEmailTemplateRepository = $companyEmailTemplateRepository;
        $this->companyLanguageService = $companyLanguageService;
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

    public function getAuthUserCompanies(Request $request): ServiceDto
    {
        $authUserId = Auth::id();
        $companies = $this->companyRepository->getByAttributes([], [
            'companyUsers' => function ($query) use ($authUserId) {
                $query->with(['roles'])->where('UserId', $authUserId);
            }
        ], ['Id', 'Name', 'CompanyName'], 'Name', false,
            [
                [
                    "relation" => "companyUsers", "column" => "UserId", "operator" => "=", "values" => $authUserId
                ]
            ]
        );


        $companies = $companies->map(function ($company) {
            $formattedCompany = collect($company)->only(['Id', 'Name', 'CompanyName'])->toArray();
            $formattedCompany['roles'] = collect($company->companyUsers[0]->roles)->pluck('Type')->toArray();
            return $formattedCompany;
        });

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

    public function cloneCompany(Request $request): ServiceDto
    {
        $relations = [
            'modules' => function ($q) {
                $q->with([
                    'tables' => function ($q) {
                        $q->with(['companyTables', 'tableFields.companyTableFields', 'tableIndices.companyTableIndices'])
                            ->whereIn('Type', ['Server', 'Both']);
                    }
                ]);
            }
        ];

        $sourceCompany = $this->companyRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $request->get('SourceCompanyId')]
        ], $relations);

        // create company
        $targetCompany = $this->companyRepository->create(array_merge(
            [
                'Name' => $request->get('Name'),
                'DomainName' => $request->get('DomainName'),
                'DatabaseName' => $request->get('DatabaseName'),
                'CompanyName' => $request->get('CompanyName'),
            ],
            collect($sourceCompany)->except([
                'Id', 'InsertTime', 'UpdateTime', 'DeleteTime', // Default Columns For laravel
                'Name', 'DomainName', 'DatabaseName', 'CompanyName', // input
                'modules', // relations
                'CustomDomainsArray' // appended
            ])->toArray()
        ));

        $this->cloneModuleTableAndFieldEntries($sourceCompany, $targetCompany);

        $targetCompany->load($relations);

        $withData = $request->get('WithData');
        $this->cloneDatabaseAndData($sourceCompany, $targetCompany, $withData);

        $withRolesAndUsers = $request->get('WithRolesAndUsers');
        list($developerRole, $adminRole, $mappedRoles) = $this->cloneRoles($sourceCompany, $targetCompany, $withRolesAndUsers);

        $mappedUsers = [];
        if (!$withRolesAndUsers) {
            $this->setUpDevelopers($targetCompany, $developerRole);
        } else {
            $mappedUsers = $this->cloneUsersWithRoles($sourceCompany, $targetCompany, $mappedRoles);
        }

        $withSettings = $request->get('WithSettings');
        $this->cloneSettings($sourceCompany, $targetCompany, $withSettings);

        if (App::environment('production')) {
            $this->setUpSyncFtp($targetCompany);
            $this->setUpImageHosting($targetCompany);
            $postmarkToken = $this->clonePostmarkEmail($sourceCompany, $targetCompany);
            if (!$withRolesAndUsers) {
                $this->createInitialUserAndSendInvitation($targetCompany, $adminRole, $postmarkToken);
            }
        }

        $this->cloneCompanyThemes($sourceCompany, $targetCompany);

        $withDataFilters = $request->get('WithDataFilters');
        $this->cloneDataFilters($sourceCompany, $targetCompany, $mappedRoles, $mappedUsers, $withRolesAndUsers, $withDataFilters);

        $withEmailConfigurations = $request->get('WithEmailConfigurations');
        $this->cloneEmailConfigurations($sourceCompany, $targetCompany, $mappedRoles, $mappedUsers, $withRolesAndUsers, $withEmailConfigurations);

        if (!$withData) {
            $this->addDefaultLanguageAndRelatedContents($targetCompany);
        }

        return new ServiceDto("Company Cloned Successfully.", 200, $targetCompany);
    }

    public function create(Request $request): ServiceDto
    {
        $company = $this->companyRepository->create($request->all());
        $this->setUpDatabase($company);
        list($developerRole, $adminRole) = $this->setUpRoles($company);
        $this->setUpDevelopers($company, $developerRole);
        if (App::environment('production')) {
            $this->setUpSyncFtp($company);
            $this->setUpImageHosting($company);
            $postmarkToken = $this->setUpPostmarkEmail($company);
            $this->createInitialUserAndSendInvitation($company, $adminRole, $postmarkToken);
        }
        $this->setUpEmailConfiguration($company);
        $this->addDefaultLanguageAndRelatedContents($company);
        return new ServiceDto("Company Created Successfully.", 200, $company);
    }

    private function setUpDatabase($company): void
    {
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
    }

    private function setUpRoles($company): array
    {
        $defaultRoles = $this->roleRepository->getByAttributes([
            ['column' => 'CompanyId', 'operand' => '=', 'value' => null]
        ]);

        return $this->createRoles($defaultRoles, $company);
    }

    /**
     * @param $roles
     * @param $company
     * @return null[]
     */
    private function createRoles($roles, $company): array
    {
        $mappedRoles = [];
        $developerRole = null;
        $adminRole = null;

        foreach ($roles as $role) {
            $newRole = $this->roleRepository->create([
                'CompanyId' => $company->Id,
                'Name' => $role->Name,
                'Type' => $role->Type,
                'Description' => $role->Description
            ]);
            if ($newRole->Type == 'Developer') {
                $developerRole = $newRole;
            }
            if ($newRole->Type == 'Administrator') {
                $adminRole = $newRole;
            }
            $mappedRole = $role;
            $mappedRole['generatedRole'] = $newRole;
            $mappedRoles[] = $mappedRole;
        }
        return [$developerRole, $adminRole, $mappedRoles];
    }

    private function setUpDevelopers($company, $developerRole): void
    {
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
    }

    private function setUpSyncFtp($company): void
    {
        $paths = ['Data/Export', 'Data/Exported', 'Data/Import', 'Data/Imported', 'Data/Templates'];
        foreach ($paths as $path) {
            Storage::disk('sync_ftp')->makeDirectory("$company->DomainName/$path");
        }
        $password = generateRandomString(12);
        $this->ftpUserRepository->create([
            'userid' => $company->DomainName,
            'passwd' => $password,
            'uid' => 33,
            'gid' => 100,
            'homedir' => "/data/import_export/$company->DomainName/Data",
            'shell' => '/bin/false',
        ]);
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

    private function setUpPostmarkEmail($company): ?string
    {
        $templateServer = $this->postmarkEmailServerRepository->firstByAttributes([
            ['column' => 'ServerName', 'operand' => '=', 'value' => 'TEMPLATE SERVER']
        ]);

        $name = $company->DomainName;
        $response = $this->postmarkRepository->createServer($name);
        if ($response['success']) {
            return $this->doPostmarkTemplateAndSettingEntry($response, $company, $templateServer);
        } else {
            Log::error("Postmark New Company Creation Error. Message: " . $response['message']);
            return null;
        }
    }

    /**
     * @param array $response
     * @param $targetCompany
     * @param Model $sourceCompanyTemplateServer
     * @return mixed
     */
    private function doPostmarkTemplateAndSettingEntry(array $response, $targetCompany, Model $sourceCompanyTemplateServer): string
    {
        $newServer = $response['data'];
        $postmarkEmailServer = $this->postmarkEmailServerRepository->create([
            'ServerId' => $newServer['ID'],
            'ServerName' => $newServer['Name'],
            'ServerDetails' => $newServer,
            'CompanyId' => $targetCompany->Id,
            'EncryptedApiToken' => encryptPostmarkToken($newServer['ApiTokens'][0])
        ]);

        // Set Token in settings
        $module = $this->moduleRepository->firstByAttributes([
            ['column' => 'Name', 'operand' => '=', 'value' => 'System']
        ]);
        $moduleSetting = $this->moduleSettingRepository->firstByAttributes([
            ['column' => 'ModuleId', 'operand' => '=', 'value' => $module->Id],
            ['column' => 'Name', 'operand' => '=', 'value' => 'PostmarkServerApiToken']
        ]);

        $this->settingRepository->create([
            'ModuleSettingId' => $moduleSetting->Id,
            'Value' => $newServer['ApiTokens'][0],
            'CompanyId' => $targetCompany->Id
        ]);

        $response = $this->postmarkRepository->pushTemplatesToAnotherServer($sourceCompanyTemplateServer->ServerId, $postmarkEmailServer->ServerId);
        if (!$response['success']) {
            Log::error("Postmark New Company Creation Error. Message: " . $response['message']);
        }

        return decryptPostmarkToken($postmarkEmailServer->EncryptedApiToken);
    }

    private function createInitialUserAndSendInvitation($company, $adminRole, $postmarkToken): void
    {
        $password = generateRandomString(8);
        $salt = generateRandomString(32, true);
        $hash = strtoupper(sha1($salt . $password));

        $CultureName = 'da-DK';

        $user = $this->userRepository->create([
            'Name' => $company->Name . " Admin",
            'Initials' => "",
            'PhoneNo' => "",
            'MobileNo' => "",
            'Email' => $company->Email,
            'Login' => $company->Email,
            'CultureName' => $CultureName,
            'Hash' => $hash,
            'Salt' => $salt,
            'Disabled' => 0,
        ]);

        $number = 1;

        $companyUser = $this->companyUserRepository->create([
            'CompanyId' => $company->Id,
            'UserId' => $user->Id,
            'Number' => $number,
            'CultureName' => $CultureName,
            'Initials' => "",
            'Territory' => "",
            'LicenceType' => "NvisionMobile",
            'Commission' => 0,
            'Billable' => 1,
            'Note' => ""
        ]);

        $this->companyUserRoleRepository->create([
            'RoleId' => $adminRole->Id,
            'CompanyUserId' => $companyUser->Id
        ]);

        $invitation = $this->userInvitationRepository->create([
            'UUID' => Str::uuid(),
            'UserName' => $user->Name,
            'UserInitials' => $user->Initials,
            'UserTerritory' => $user->UserTerritory,
            'Email' => $user->Email,
            'LicenceType' => $companyUser->LicenceType,
            'Note' => $user->Note,
            'CompanyId' => $company->Id,
            'Roles' => $adminRole->Id,
            'SentByUSerId' => Auth::id(),
            'ExpiryDays' => 2
        ]);

        $this->sendInvitationMail($company, $invitation, $postmarkToken);
    }

    private function sendInvitationMail($company, $invitation, $postmarkToken): void
    {
        $url = env('NSALES_OFFICE_APP_URL') . '/account-setup?token=' . $invitation->UUID;
        $postmarkTemplateData = [
            "Name" => $invitation->UserName,
            "CompanyName" => $company->Name,
            "action_url" => $url
        ];

        $postmarkTemplateIdOrAlias = 'sales-rep-set-password';
        $response = $this->postmarkRepository->sendEmailWithTemplate("no-reply@nsales.dk", $company->Email, $postmarkTemplateIdOrAlias, $postmarkTemplateData, null, null, $postmarkToken);
        if (!$response['success']) {
            Log::error("Postmark New Company Creation Error. Message: " . $response['message']);
        }
    }

    private function setUpEmailConfiguration($company): void
    {
        $orderModule = $this->moduleRepository->firstByAttributes([
            ['column' => 'Name', 'operand' => '=', 'value' => 'Order']
        ]);
        $this->emailConfigurationRepository->create([
            'Name' => "Order Confirmation",
            'TemplateType' => "Internal",
            'Disabled' => 0,
            'From' => "no-reply@nsales.dk",
            'To' => "",
            'Cc' => "",
            'Bcc' => "",
            'SendToCompany' => 0,
            'SendToUser' => 1,
            'SendToCustomer' => 1,
            'SendToSupplier' => 0,
            'SendToEmployee' => 0,
            'Subject' => "",
            'Body' => "",
            'Description' => "",
            'TemplatePath' => "",
            'ModuleId' => $orderModule->Id,
            'ApplicationId' => null,
            'CompanyId' => $company->Id,
            'RoleId' => null,
            'CompanyUserId' => null,
        ]);
    }

    private function addDefaultLanguageAndRelatedContents($company): void
    {
        $language = $this->languageRepository->firstByAttributes([
            ['column' => 'IsDefault', 'operand' => '=', 'value' => 1]
        ]);
        CompanyService::setCompanyDatabaseConnection($company->Id);
        $companyLanguage = $this->companyLanguageService->createCompanyLanguage($language, 1);
        $this->companyLanguageService->addCompanyTranslations($language, $companyLanguage);
        $this->companyLanguageService->addCompanyEmailLayoutAndTemplates($language, $companyLanguage);
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

    private function cloneModuleTableAndFieldEntries($sourceCompany, $targetCompany): void
    {
        // Entry in CompanyModule, CompanyTable, CompanyTableField, CompanyTableIndex table
        foreach ($sourceCompany->modules as $module) {
            $this->makeEntryInCompanyModuleTable($targetCompany->Id, $module->Id);
            foreach ($module->tables as $table) {
                // make entry in CompanyTable table
                if ($table->companyTables->count() > 0) {
                    $companyIds = $table->companyTables->pluck('CompanyId')->toArray();
                    if (in_array($sourceCompany->Id, $companyIds)) {
                        $this->makeEntryInCompanyTableTable($targetCompany->Id, $table->Id);
                    }
                }

                // make entries in CompanyTableField table
                foreach ($table->tableFields as $tableField) {
                    if ($tableField->companyTableFields->count() > 0) {
                        $companyIds = $tableField->companyTableFields->pluck('CompanyId')->toArray();
                        if (in_array($sourceCompany->Id, $companyIds)) {
                            $this->makeEntryInCompanyTableFieldTable($targetCompany->Id, $tableField->Id);
                        }
                    }
                }

                // make entries in CompanyTableIndex table
                foreach ($table->tableIndices as $tableIndex) {
                    if ($tableIndex->companyTableIndices->count() > 0) {
                        $companyIds = $tableIndex->companyTableIndices->pluck('CompanyId')->toArray();
                        if (in_array($sourceCompany->Id, $companyIds)) {
                            $this->makeEntryInCompanyTableIndexTable($targetCompany->Id, $tableIndex->Id);
                        }
                    }
                }

            }
        }
    }

    public function cloneDatabaseAndData($sourceCompany, $targetCompany, $withData): void
    {
        $excludesModulesForData = ['Order', 'ActivityLog'];
        $sqlQueries = [];
        // create cloning company database
        $sqlQueries[] = MysqlQueryGenerator::getCreateDatabaseSql($targetCompany->DatabaseName);
        $sqlQueries[] = "SET SQL_MODE='ALLOW_INVALID_DATES';";
        foreach ($sourceCompany->modules as $module) {
            foreach ($module->tables as $table) {
                if ($table->companyTables->count()) {
                    $companyTableCompanyIds = $table->companyTables->pluck('CompanyId')->toArray();
                    if (in_array($sourceCompany->Id, $companyTableCompanyIds)) {
                        $sqlQueries[] = MysqlQueryGenerator::getCopyTableStructureSql(
                            $sourceCompany->DatabaseName,
                            $table->Name,
                            $targetCompany->DatabaseName,
                            $table->Name
                        );
                        if ($withData && !in_array($module->Name, $excludesModulesForData)) {
                            $sqlQueries[] = MysqlQueryGenerator::getCopyTableDataSql(
                                $sourceCompany->DatabaseName,
                                $table->Name,
                                $targetCompany->DatabaseName,
                                $table->Name
                            );
                        }
                    }
                } else {
                    $sqlQueries[] = MysqlQueryGenerator::getCopyTableStructureSql(
                        $sourceCompany->DatabaseName,
                        $table->Name,
                        $targetCompany->DatabaseName,
                        $table->Name
                    );
                    if ($withData && !in_array($module->Name, $excludesModulesForData)) {
                        $sqlQueries[] = MysqlQueryGenerator::getCopyTableDataSql(
                            $sourceCompany->DatabaseName,
                            $table->Name,
                            $targetCompany->DatabaseName,
                            $table->Name
                        );
                    }
                }
            }
        }

        foreach ($sqlQueries as $sqlQuery) {
            try {
                DB::statement($sqlQuery);
            } catch (Exception $exception) {
                Log::error("Clone Company Database Error. Message: {$exception->getMessage()}");
            }
        }
    }

    private function cloneRoles($sourceCompany, $targetCompany, $withRolesAndUsers = true): array
    {
        if (!$withRolesAndUsers) {
            return $this->setUpRoles($targetCompany);
        }

        $sourceCompanyRoles = $this->roleRepository->getByAttributes([
            ['column' => 'CompanyId', 'operand' => '=', 'value' => $sourceCompany->Id]
        ]);

        return $this->createRoles($sourceCompanyRoles, $targetCompany);
    }

    private function cloneUsersWithRoles($sourceCompany, $targetCompany, $mappedRoles): array
    {
        $mappedRoles = collect($mappedRoles);
        $mappedUsers = [];

        $relations = [
            'companyUser' => function ($q) use ($sourceCompany) {
                $q->with(['roles'])->where('CompanyId', $sourceCompany->Id);
            }
        ];
        $filterByRelations = [
            ["relation" => 'companyUser', "column" => "CompanyId", "operator" => "=", "values" => $sourceCompany->Id],
        ];
        $users = $this->userRepository->getByAttributes([], $relations, '', '', false, $filterByRelations);

        foreach ($users as $user) {
            $newCompanyUser = $this->companyUserRepository->create([
                'CompanyId' => $targetCompany->Id,
                'UserId' => $user->Id,
                'Number' => $user->companyUser->Number,
                'CultureName' => $user->companyUser->CultureName,
                'Initials' => $user->companyUser->Initials,
                'Territory' => $user->companyUser->Territory,
                'Commission' => $user->companyUser->Commission,
                'Billable' => $user->companyUser->Billable,
                'Note' => $user->companyUser->Note,
                'LicenceType' => $user->companyUser->LicenceType,
            ]);
            foreach ($user->companyUser->roles as $role) {
                $mappedRole = $mappedRoles->where('Id', $role->Id)->first();
                $this->companyUserRoleRepository->create([
                    'RoleId' => $mappedRole['generatedRole']['Id'],
                    'CompanyUserId' => $newCompanyUser->Id,
                ]);
            }

            $user->generatedCompanyUser = $newCompanyUser;

            $mappedUsers[] = $user;
        }

        return $mappedUsers;
    }

    private function cloneSettings($sourceCompany, $targetCompany, $withSettings): void
    {
        if ($withSettings) {
            $baseSettings = $this->settingRepository->getByAttributes([
                ['column' => 'CompanyId', 'operand' => '=', 'value' => $sourceCompany->Id]
            ]);
            if ($baseSettings->count()) {
                $baseSettings->transform(function ($setting) use ($targetCompany) {
                    $setting->CompanyId = $targetCompany->Id;
                    return collect($setting)->except(['Id', 'UpdateTime', 'InsertTime', 'DeleteTime'])->toArray();
                });
                $this->settingRepository->insert($baseSettings->toArray());
            }
        }
    }

    private function clonePostmarkEmail($sourceCompany, $targetCompany): ?string
    {
        $sourceCompanyTemplateServer = $this->postmarkEmailServerRepository->firstByAttributes([
            ['column' => 'CompanyId', 'operand' => '=', 'value' => $sourceCompany->Id]
        ]);

        // No server for current company then copy templates form default template server
        if (!$sourceCompanyTemplateServer) {
            $sourceCompanyTemplateServer = $this->postmarkEmailServerRepository->firstByAttributes([
                ['column' => 'ServerName', 'operand' => '=', 'value' => 'TEMPLATE SERVER']
            ]);
        }

        $name = $targetCompany->DomainName;
        $response = $this->postmarkRepository->createServer($name);
        if ($response['success']) {
            return $this->doPostmarkTemplateAndSettingEntry($response, $targetCompany, $sourceCompanyTemplateServer);
        } else {
            Log::error("Postmark Clone Company Error. Message: " . $response['message']);
            return null;
        }
    }

    private function cloneCompanyThemes($sourceCompany, $targetCompany): void
    {
        $companyThemes = $this->companyThemeRepository->getByAttributes([
            ['column' => 'CompanyId', 'operand' => '=', 'value' => $sourceCompany->Id]
        ]);
        if ($companyThemes->count()) {
            $companyThemes->transform(function ($theme) use ($targetCompany) {
                $theme->CompanyId = $targetCompany->Id;
                return collect($theme)->except(['Id', 'UpdateTime', 'InsertTime', 'DeleteTime'])->toArray();
            });
            $this->companyThemeRepository->insert($companyThemes->toArray());
        }
    }

    private function cloneDataFilters($sourceCompany, $targetCompany, $mappedRoles, $mappedUsers, $withRolesAndUsers, $withDataFilters): void
    {
        if ($withDataFilters) {
            $sourceRoleIds = [];
            $sourceCompanyUserIds = [];
            if ($withRolesAndUsers) {
                $sourceRoleIds = collect($mappedRoles)->pluck('Id')->toArray();
                $sourceCompanyUserIds = collect($mappedUsers)->pluck('companyUser.Id')->toArray();
            }
            $sourceDataFilters = $this->dataFilterRepository->getModel()
                ->where(function ($query) use ($sourceCompany, $sourceRoleIds, $sourceCompanyUserIds) {
                    $query->where('CompanyId', $sourceCompany->Id)
                        ->orWhere(function ($query) use ($sourceRoleIds) {
                            if ($sourceRoleIds) {
                                $query->whereIn('RoleId', $sourceRoleIds);
                            }
                        })
                        ->orWhere(function ($query) use ($sourceCompanyUserIds) {
                            if ($sourceCompanyUserIds) {
                                $query->whereIn('CompanyUserId', $sourceCompanyUserIds);
                            }
                        });
                })
                ->get();

            $sourceDataFilters->transform(function ($sourceDataFilter) use ($mappedRoles, $mappedUsers, $targetCompany) {
                if ($sourceDataFilter->ApplyTo == 'Role') {
                    $sourceDataFilter->RoleId = collect($mappedRoles)->where('Id', $sourceDataFilter->RoleId)->first()['generatedRole']['Id'];
                } elseif ($sourceDataFilter->ApplyTo == 'User') {
                    $sourceDataFilter->CompanyUserId = collect($mappedUsers)->where('companyUser.Id', $sourceDataFilter->CompanyUserId)->first()['generatedCompanyUser']['Id'];
                } else {
                    $sourceDataFilter->CompanyId = $targetCompany->Id;
                }
                return collect($sourceDataFilter)->except(['Id', 'UpdateTime', 'InsertTime', 'DeleteTime', 'ApplyTo'])->toArray();
            });

            $this->dataFilterRepository->insert($sourceDataFilters->toArray());
        }
    }

    private function cloneEmailConfigurations($sourceCompany, $targetCompany, $mappedRoles, $mappedUsers, $withRolesAndUsers, $withEmailConfigurations): void
    {
        if ($withEmailConfigurations) {
            $sourceRoleIds = [];
            $sourceCompanyUserIds = [];
            if ($withRolesAndUsers) {
                $sourceRoleIds = collect($mappedRoles)->pluck('Id')->toArray();
                $sourceCompanyUserIds = collect($mappedUsers)->pluck('companyUser.Id')->toArray();
            }
            $sourceEmailConfigurations = $this->emailConfigurationRepository->getModel()
                ->where(function ($query) use ($sourceCompany, $sourceRoleIds, $sourceCompanyUserIds) {
                    $query->where('CompanyId', $sourceCompany->Id)
                        ->orWhere(function ($query) use ($sourceRoleIds) {
                            if ($sourceRoleIds) {
                                $query->whereIn('RoleId', $sourceRoleIds);
                            }
                        })
                        ->orWhere(function ($query) use ($sourceCompanyUserIds) {
                            if ($sourceCompanyUserIds) {
                                $query->whereIn('CompanyUserId', $sourceCompanyUserIds);
                            }
                        });
                })
                ->get();

            $sourceEmailConfigurations->transform(function ($sourceEmailConfiguration) use ($mappedRoles, $mappedUsers, $targetCompany) {
                if ($sourceEmailConfiguration->ApplyTo == 'Role') {
                    $sourceEmailConfiguration->RoleId = collect($mappedRoles)->where('Id', $sourceEmailConfiguration->RoleId)->first()['generatedRole']['Id'];
                } elseif ($sourceEmailConfiguration->ApplyTo == 'User') {
                    $sourceEmailConfiguration->CompanyUserId = collect($mappedUsers)->where('companyUser.Id', $sourceEmailConfiguration->CompanyUserId)->first()['generatedCompanyUser']['Id'];
                } else {
                    $sourceEmailConfiguration->CompanyId = $targetCompany->Id;
                }
                return collect($sourceEmailConfiguration)->except(['Id', 'UpdateTime', 'InsertTime', 'DeleteTime', 'ApplyTo'])->toArray();
            });

            $this->emailConfigurationRepository->insert($sourceEmailConfigurations->toArray());
        } else {
            $this->setUpEmailConfiguration($targetCompany);
        }
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
        ], ["imageHostAccount", "postmarkEmailServer"]);

        $sqlQuery = MysqlQueryGenerator::getDropDatabaseSql($company->DatabaseName);
        try {
            DB::statement($sqlQuery);
        } catch (Exception $exception) {
            Log::error("Delete Company Database Error. Message: {$exception->getMessage()}");
        }

        if (App::environment('production')) {
            // Delete Sync Ftp User and Folder
            $this->ftpUserRepository->deleteByAttributes([
                ['column' => 'userid', 'operand' => '=', 'value' => $company->DomainName]
            ]);
            Storage::disk('sync_ftp')->deleteDirectory("$company->DomainName");

            // Delete Image Host Account (CDN)
            if ($company->imageHostAccount) {
                // If this CDN is associated with only one company then delete
                // Otherwise only delete from DB and keep the Image Host Account (storage zone, cdn)
                $imageHostAccounts = $this->imageHostAccountRepository->getByAttributes([
                    ['column' => 'StorageZoneId', 'operand' => '=', 'value' => $company->imageHostAccount->StorageZoneId],
                ]);
                if ($imageHostAccounts->count() == 1) {
                    // Delete Image Hosting Account
                    $this->bunnyCdnRepository->deleteStorageZone($company->imageHostAccount->StorageZoneId);
                }

                // Delete Entry From DB
                $this->imageHostAccountRepository->findByIdAndDelete($company->imageHostAccount->Id);
            }

            // Delete Mail Server
            if ($company->postmarkEmailServer) {
                // Delete From DB
                $this->postmarkEmailServerRepository->findByIdAndDelete($company->postmarkEmailServer->Id);
                //TODO
                // Delete PostMark Server
            }
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

    public function getCompanyCustomDomains(Request $request): ServiceDto
    {
        $company = $this->companyRepository->findById($request->get('CompanyId'));

        return new ServiceDto("Company Custom Domain Retrieved Successfully.", 200, $company->CustomDomainsArray);
    }

    /**
     * @throws GuzzleException
     */
    public function addCompanyCustomDomain(Request $request): ServiceDto
    {
        $response = $this->nsalesAdminDjangoApiRepository->addCompanyCustomDomain($request->get('CustomDomain'), $request->get('UUID'));
        if (!$response['success']) {
            $errorData = [];
            $statusCode = $response['status_code'];
            if ($response['status_code'] === 400 && isset($response['errors']['hostname'])) {
                $statusCode = 422;
                $errorData['CustomDomain'] = $response['errors']['hostname'];
            }

            return new ServiceDto($response["message"], $statusCode, $errorData);
        }

        $companyCustomDomains = $this->updateCompanyDomainToDB($request);

        return new ServiceDto('Custom Domain Added Successfully.', 200, $companyCustomDomains);
    }

    public function updateCompanyDomainToDB($request)
    {
        $company = $this->companyRepository->findById($request->get('CompanyId'));
        $companyCustomDomains = $company->CustomDomainsArray;
        $companyCustomDomains[] = $request->get('CustomDomain');
        $updateCompany = $this->companyRepository->findByIdAndUpdate(
            $request->get('CompanyId'),
            [
                'CustomDomains' => $companyCustomDomains,
            ]
        );

        return $updateCompany->CustomDomainsArray;
    }


    public function getPostmarkServer(Request $request): ServiceDto
    {
        $postmarkServer = $this->postmarkEmailServerRepository->firstByAttributes([
            ['column' => 'CompanyId', 'operand' => '=', 'value' => $request->get('CompanyId')]
        ]);
        $postmarkServer = $postmarkServer?->only(['ServerName', 'ServerId', 'ServerLink']);
        return new ServiceDto('Postmark Server Retrieved Successfully.', 200, $postmarkServer ?? []);
    }

    public function createPostmarkServer(Request $request): ServiceDto
    {
        $company = $this->companyRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $request->get('CompanyId')]
        ]);
        $postmarkToken = $this->setUpPostmarkEmail($company);
        if ($postmarkToken) {
            $postmarkServer = $this->postmarkEmailServerRepository->firstByAttributes([
                ['column' => 'CompanyId', 'operand' => '=', 'value' => $request->get('CompanyId')]
            ])->only(['ServerName', 'ServerId', 'ServerLink']);
            return new ServiceDto('Postmark Server Added Successfully.', 200, $postmarkServer);
        } else {
            return new ServiceDto('Postmark Server not created.', 500, []);
        }
    }

    /**
     * @throws GuzzleException
     */
    public function deleteCompanyCustomDomain(Request $request): ServiceDto
    {
        if ($request->has('HostId')) {
            $response = $this->nsalesAdminDjangoApiRepository->deleteCompanyCustomDomain($request->get('HostId'));

            if (!$response['success']) {
                return new ServiceDto($response["message"], $response['status_code'], []);
            }
        }
        $companyCustomDomains = $this->deleteCompanyDomainToDB($request);

        return new ServiceDto('Custom Domain Deleted Successfully.', 200, $companyCustomDomains);
    }

    public function deleteCompanyDomainToDB($request)
    {
        $company = $this->companyRepository->findById($request->get('CompanyId'));
        $companyCustomDomains = $company->CustomDomainsArray;
        $companyCustomDomains = array_filter(
            $companyCustomDomains,
            function ($domain) use ($request) {
                return $domain !== $request->get('CustomDomain');
            }
        );

        $updateCompany = $this->companyRepository->findByIdAndUpdate(
            $request->get('CompanyId'),
            [
                'CustomDomains' => $companyCustomDomains,
            ]
        );

        return $updateCompany->CustomDomainsArray;
    }

}
