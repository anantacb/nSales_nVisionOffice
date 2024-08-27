<?php

namespace App\Console\Commands;

use App\Repositories\Eloquent\Company\CompanyLanguage\CompanyLanguageRepositoryInterface;
use App\Repositories\Eloquent\Company\CompanyTranslation\CompanyTranslationRepositoryInterface;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyModule\CompanyModuleRepositoryInterface;
use App\Repositories\Eloquent\Office\Language\LanguageRepositoryInterface;
use App\Repositories\Eloquent\Office\Module\ModuleRepositoryInterface;
use App\Repositories\Eloquent\Office\Translation\TranslationRepositoryInterface;
use App\Services\Company\CompanyService;
use App\Services\Traits\ModuleHelperTrait;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AddModuleToCompanies extends Command
{
    use ModuleHelperTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:activate {--moduleName=} {--withSubmodules} {--companyId=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate specific module in all companies or selected companies.';

    protected CompanyRepositoryInterface $companyRepository;
    protected ModuleRepositoryInterface $moduleRepository;
    protected CompanyModuleRepositoryInterface $companyModuleRepository;
    protected LanguageRepositoryInterface $languageRepository;
    protected TranslationRepositoryInterface $translationRepository;
    protected CompanyLanguageRepositoryInterface $companyLanguageRepository;
    protected CompanyTranslationRepositoryInterface $companyTranslationRepository;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->init();

        $moduleName = $this->option('moduleName');
        $withSubmodules = $this->option('withSubmodules');

        $relations = [
            'subModules' => function ($q) {
                $q->with(['tables' => function ($q) {
                    $q->with(['companyTables', 'tableFields.companyTableFields', 'tableIndices.companyTableIndices'])
                        ->whereIn('Type', ['Server', 'Both']);
                }]);
            },
            'tables' => function ($q) {
                $q->with(['companyTables', 'tableFields.companyTableFields', 'tableIndices.companyTableIndices'])
                    ->whereIn('Type', ['Server', 'Both']);
            }];

        $module = $this->moduleRepository->firstByAttributes([
            ['column' => 'Name', 'operand' => '=', 'value' => $moduleName],
            ['column' => 'Type', 'operand' => '=', 'value' => ['Standard', 'Extension']],
        ], $relations);

        $inputCompanyIds = $this->option('companyId');

        $installedCompanyIds = $this->companyModuleRepository->getByAttributes([
            ['column' => 'ModuleId', 'operand' => '=', 'value' => $module->Id]
        ], [], ['CompanyId'])->pluck('CompanyId')->toArray();

        // If no Company selected then install module to all except already installed companies
        if (empty($inputCompanyIds)) {
            $companies = $this->companyRepository->getByAttributes([
                ['column' => 'Id', 'operand' => '!=', 'value' => $installedCompanyIds]
            ]);
        } else {
            $companies = $this->companyRepository->getByAttributes([
                ['column' => 'Id', 'operand' => '=', 'value' => $inputCompanyIds],
                ['column' => 'Id', 'operand' => '!=', 'value' => $installedCompanyIds]
            ]);
        }

        /**
         * For Each Company
         */
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

        $bar = $this->output->createProgressBar($companies->count());
        $bar->start();

        $statusHeaders = ['Name', 'Id', 'Status'];
        $statusRows = [];

        $language = $this->languageRepository->firstByAttributes([
            ['column' => 'IsDefault', 'operand' => '=', 'value' => 1]
        ]);
        $translations = $this->translationRepository->getByAttribute('LanguageId', '=', $language->Id);

        foreach ($companies as $company) {
            // Has Sub Modules And Install
            if ($withSubmodules) {
                // Entry In CompanyModule table
                $subModuleIds = $module->subModules->pluck('Id')->toArray();
                $moduleAndSubModuleIds = array_merge([$module->Id], $subModuleIds);
                foreach ($moduleAndSubModuleIds as $moduleId) {
                    $this->makeEntryInCompanyModuleTable($company->Id, $moduleId);
                }

                // Retrieve all tables of module and submodule
                $sqlQueries = $this->getModulesCreateTableSqlQueries($module, $company);
                foreach ($module->subModules as $subModule) {
                    $subModulesTableCreationQueries = $this->getModulesCreateTableSqlQueries($subModule, $company);
                    $sqlQueries = array_merge($sqlQueries, $subModulesTableCreationQueries);
                }
            } else {
                // Entry In CompanyModule table
                $this->makeEntryInCompanyModuleTable($company->Id, $module->Id);
                $sqlQueries = $this->getModulesCreateTableSqlQueries($module, $company);
            }
            $failed = false;
            foreach ($sqlQueries as $sqlQuery) {
                try {
                    DB::statement($sqlQuery);
                } catch (Exception $exception) {
                    $failed = true;
                    Log::error("Add Module To Company. Message: " . $exception->getMessage());
                }
            }

            if ($failed) {
                $statusRows[] = [$company->Name, $company->Id, 'Failed'];
            } else {
                if ($moduleName == 'Translation') {
                    CompanyService::setCompanyDatabaseConnection($company->Id);
                    $companyLanguage = $this->companyLanguageRepository->firstOrCreate([
                        'Name' => $language->Name,
                        'Locale' => $language->Locale,
                        'Code' => $language->Code,
                        'IsDefault' => $language->IsDefault,
                    ]);
                    foreach ($translations as $translation) {
                        $this->companyTranslationRepository->firstOrCreate([
                            'CompanyLanguageId' => $companyLanguage->Id,
                            'Type' => $translation->Type,
                            'ElementName' => $translation->ElementName
                        ], [
                            'Translations' => $translation->Translations
                        ]);
                    }
                }
                $statusRows[] = [$company->Name, $company->Id, 'Successful'];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info(PHP_EOL);
        $this->table($statusHeaders, $statusRows);

        return Command::SUCCESS;
    }

    private function init(): void
    {
        $this->companyRepository = app(CompanyRepositoryInterface::class);
        $this->moduleRepository = app(ModuleRepositoryInterface::class);
        $this->companyModuleRepository = app(CompanyModuleRepositoryInterface::class);
        $this->languageRepository = app(LanguageRepositoryInterface::class);
        $this->translationRepository = app(TranslationRepositoryInterface::class);
        $this->companyLanguageRepository = app(CompanyLanguageRepositoryInterface::class);
        $this->companyTranslationRepository = app(CompanyTranslationRepositoryInterface::class);
    }
}
