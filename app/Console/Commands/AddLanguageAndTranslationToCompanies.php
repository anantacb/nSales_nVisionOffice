<?php

namespace App\Console\Commands;

use App\Models\Company\CompanyTranslation;
use App\Repositories\Eloquent\Company\CompanyLanguage\CompanyLanguageRepositoryInterface;
use App\Repositories\Eloquent\Company\CompanyTranslation\CompanyTranslationRepositoryInterface;
use App\Repositories\Eloquent\Company\WebShopLanguage\WebShopLanguageRepositoryInterface;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyModule\CompanyModuleRepositoryInterface;
use App\Repositories\Eloquent\Office\Language\LanguageRepositoryInterface;
use App\Repositories\Eloquent\Office\Module\ModuleRepositoryInterface;
use App\Repositories\Eloquent\Office\Translation\TranslationRepositoryInterface;
use App\Services\Company\CompanyService;
use App\Services\Traits\ModuleHelperTrait;
use Illuminate\Console\Command;

class AddLanguageAndTranslationToCompanies extends Command
{
    use ModuleHelperTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'languageTranslation:add {--C|companyId=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert data into table:CompanyLanguage, CompanyTranslation for all companies or selected companies.';

    protected CompanyRepositoryInterface $companyRepository;
    protected ModuleRepositoryInterface $moduleRepository;
    protected CompanyModuleRepositoryInterface $companyModuleRepository;
    protected LanguageRepositoryInterface $languageRepository;
    protected TranslationRepositoryInterface $translationRepository;
    protected CompanyLanguageRepositoryInterface $companyLanguageRepository;
    protected CompanyTranslationRepositoryInterface $companyTranslationRepository;
    protected WebShopLanguageRepositoryInterface $webShopLanguageRepository;


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->init();

        $moduleNames = ['Translation', 'WSLanguage'];
        $inputCompanyIds = $this->option('companyId');

//        $modules = $this->moduleRepository->firstByAttributes([
        $moduleIds = $this->moduleRepository->getByAttributes([
            ['column' => 'Name', 'operand' => '=', 'value' => $moduleNames],
            ['column' => 'Type', 'operand' => '=', 'value' => ['Standard', 'Extension']],
        ])->pluck('Id')->toArray();

//        dd($moduleIds);

        $installedCompanyIds = $this->companyModuleRepository->getByAttributes([
            ['column' => 'ModuleId', 'operand' => '=', 'value' => $moduleIds]
        ], [], ['CompanyId'])->pluck('CompanyId')->toArray();

        if (empty($inputCompanyIds)) {
            $companies = $this->companyRepository->getByAttributes([
                ['column' => 'Id', 'operand' => '=', 'value' => $installedCompanyIds]
            ]);
        } else {
            $companies = $this->companyRepository->getByAttributes([
                ['column' => 'Id', 'operand' => '=', 'value' => $inputCompanyIds],
                ['column' => 'Id', 'operand' => '=', 'value' => $installedCompanyIds]
            ]);
        }

        /**
         * For Each Company
         */
        /**
         * Language
         *       ->compare WebShopLanguage table with CompanyLanguage
         *       ->insert row if language is not present into CompanyLanguage table
         * Translation
         *      ->delete all translations if language is not present in CompanyLanguage
         *      ->compare Translation table with CompanyTranslation
         *      ->insert row if Translation is not present into CompanyTranslation table
         */

        $bar = $this->output->createProgressBar($companies->count());
        $bar->start();

        $statusHeaders = ['Name', 'Id', 'Language', 'Translations', 'Deleted Translations', 'Status'];
        $statusRows = [];

        $officeLanguages = $this->languageRepository->all();
        $officeTranslations = $this->translationRepository->all();

        foreach ($companies as $company) {
            CompanyService::setCompanyDatabaseConnection($company->Id);

            $companyLanguages = $this->companyLanguageRepository->all();
            $webShopLanguages = $this->webShopLanguageRepository->getByAttributes([
                ['column' => 'Disabled', 'operand' => '=', 'value' => 0],
            ]);

//            foreach ($languages as $language) {
//                $matchingCompanyLanguage = $companyLanguages->firstWhere('Code', $language->Code);
//                $matchingWebShopLanguage = $webShopLanguages->firstWhere('Code', $language->Code);
//
//                if (
//                    $matchingWebShopLanguage && $matchingCompanyLanguage &&
//                    $matchingWebShopLanguage->Code !== $matchingCompanyLanguage->Code
//                ) {
//                    $this->companyLanguageRepository->create([
//                        'Name' => $language->Name,
//                        'Locale' => $language->Locale,
//                        'Code' => $language->Code,
//                        'IsDefault' => $language->IsDefault,
//                    ]);
//                }
//            }

//            $filteredWebShopLanguages = new Collection();

            $filteredWebShopLanguages = $webShopLanguages->filter(function ($webShopLanguage) use ($companyLanguages) {
                return !$companyLanguages->contains('Code', $webShopLanguage->Code);
            });

//            $result = CompanyTranslation::whereNotIn('CompanyLanguageId', function($query) {
//                $query->select('Id')->from('CompanyLanguage');
//            })->get();

            // delete translations if language is not present in CompanyLanguage table
//            $deletedTranslations = CompanyTranslation::whereNotIn('CompanyLanguageId', function ($query) {
//                $query->select('Id')->from('CompanyLanguage');
//            })->delete();

            $languageDataToInsert = [];
            $translationDataToInsert = [];
            $status = 'No Changes';
            $languageStatus = 'No Changes';
            $translationsStatus = 'No Changes';
            $statusRow = [$company->Name, $company->Id, $languageStatus, $translationsStatus, $status];

            foreach ($filteredWebShopLanguages as $webShopLanguage) {
                $language = $officeLanguages->where('Code', $webShopLanguage->Code)->first();
                if ($language) {
                    // insert Language into CompanyLanguage
                    $insertedCompanyLanguage = $this->companyLanguageRepository->firstOrCreate([
                        'Code' => $language->Code,
                    ], [
                        'Name' => $language->Name,
                        'Locale' => $language->Locale,
                        'IsDefault' => $language->IsDefault,
                    ]);
                    $languageStatus = $insertedCompanyLanguage ? 'Successful' : 'Failure';

//                    $languageDataToInsert[] = [
//                        'Name' => $language->Name,
//                        'Locale' => $language->Locale,
//                        'Code' => $language->Code,
//                        'IsDefault' => $language->IsDefault,
//                    ];

                    $translations = $officeTranslations->where('LanguageId', '=', $language->Id)->all();

                    $insertedCompanyTranslation = false;
                    foreach ($translations as $translation) {
                        // insert Language into CompanyTranslation
                        $insertedCompanyTranslation = $this->companyTranslationRepository->firstOrCreate([
                            'CompanyLanguageId' => $insertedCompanyLanguage->Id,
                            'Type' => $translation->Type,
                            'ElementName' => $translation->ElementName
                        ], [
                            'Translations' => $translation->Translations
                        ]);

//                        $translationDataToInsert[] = [
//                            'CompanyLanguageId' => $insertedCompanyLanguage->Id,
//                            'Type' => $translation->Type,
//                            'ElementName' => $translation->ElementName,
//                            'Translations' => $translation->Translations
//                        ];
                    }
                    $translationsStatus = $insertedCompanyTranslation ? 'Successful' : 'Failure';
                    $status = 'Successful';
                }

                $statusRow = [$company->Name, $company->Id, $languageStatus, $translationsStatus, $status];
            }
//            dd($translationDataToInsert);
//            dd($languageDataToInsert, $translationDataToInsert, $filteredWebShopLanguages);
            $statusRows[] = $statusRow;
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
        $this->webShopLanguageRepository = app(WebShopLanguageRepositoryInterface::class);
    }
}
