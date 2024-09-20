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
use Illuminate\Database\Eloquent\Collection;

class AddLanguageAndTranslationToCompanies extends Command
{
    use ModuleHelperTrait;

    /**
     * The name and signature of the console command.
     *
     *  php artisan languageTranslation:add
     *  php artisan languageTranslation:add -C821 -C943
     *  php artisan languageTranslation:add --companyId=821 --companyId=943
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
    protected Collection $officeLanguages;
    protected Collection $officeTranslations;

    /**
     * Execute the console command.
     * @return int
     */
    public function handle(): int
    {
        $this->init();

        //$moduleNames = ['Translation', 'WSLanguage'];
        $moduleNames = ['WSLanguage'];
        $inputCompanyIds = $this->option('companyId');
        $ignoredCompanyIds = ['691', '957'];        // skip Company: FLEYE, FLEYE TEST

        $moduleIds = $this->moduleRepository->getByAttributes([
            ['column' => 'Name', 'operand' => '=', 'value' => $moduleNames],
            ['column' => 'Type', 'operand' => '=', 'value' => ['Standard', 'Extension']],
        ])->pluck('Id')->toArray();

        $wsLanguageCompanyIds = $this->companyModuleRepository->getByAttributes([
            ['column' => 'ModuleId', 'operand' => '=', 'value' => $moduleIds]
        ], [], ['CompanyId'])->pluck('CompanyId')->toArray();

        $attributes = [
            ['column' => 'Disabled', 'operand' => '=', 'value' => '0'],
            ['column' => 'Id', 'operand' => '!=', 'value' => $ignoredCompanyIds]
        ];

        if (!empty($inputCompanyIds)) {
            $attributes[] = ['column' => 'Id', 'operand' => '=', 'value' => $inputCompanyIds];
        }

        $companies = $this->companyRepository->getByAttributes($attributes);
        $this->officeLanguages = $this->languageRepository->all();
        $this->officeTranslations = $this->translationRepository->all();

        $bar = $this->output->createProgressBar($companies->count());
        $bar->start();
        $statusHeaders = ['Name', 'Id', 'isWSLanguageEnabled', 'Language', 'Translations', 'Status'];
        $statusRows = [];

        /**
         * For Each Company
         */
        /**
         *
         * If Company has WSLanguage module
         *      Language
         *          ->compare WebShopLanguage table with CompanyLanguage
         *          ->insert row if language is not present into CompanyLanguage table
         *
         * Translation
         *      ->Truncate CompanyTranslation table
         *      ->get all the languages from CompanyLanguage table
         *      ->FOR EACH CompanyLanguage
         *          ->get all the translations form Translations table
         *          ->insert row into CompanyTranslation table for all CompanyLanguage
         *
         */

        foreach ($companies as $company) {
            $status = 'No Changes';
            $languageStatus = 'No Changes';
            $translationsStatus = 'No Changes';
            $isWSLanguageEnabled = 'Not Active';

            CompanyService::setCompanyDatabaseConnection($company->Id);

            // If Company has WSLanguage module
            if (in_array($company->Id, $wsLanguageCompanyIds)) {
                $isWSLanguageEnabled = 'Active';
                $companyLanguages = $this->companyLanguageRepository->all();
                $webShopLanguages = $this->webShopLanguageRepository->getByAttributes([
                    ['column' => 'Disabled', 'operand' => '=', 'value' => 0],
                ]);

                $filteredWebShopLanguages = $webShopLanguages->filter(function ($webShopLanguage) use ($companyLanguages) {
                    return !$companyLanguages->contains('Code', $webShopLanguage->Code);
                });

                foreach ($filteredWebShopLanguages as $webShopLanguage) {
                    $officeLanguage = $this->officeLanguages->where('Code', $webShopLanguage->Code)->first();
                    if ($officeLanguage) {
                        // insert Language into CompanyLanguage
                        $insertedCompanyLanguage = $this->companyLanguageRepository->firstOrCreate([
                            'Code' => $officeLanguage->Code,
                        ], [
                            'Name' => $officeLanguage->Name,
                            'Locale' => $officeLanguage->Locale,
                            'IsDefault' => $officeLanguage->IsDefault,
                        ]);

                        $languageStatus = $insertedCompanyLanguage ? 'Successful' : 'Failure';
                    }
                }
            }

            // For all Company Insert Translations
            $translationsStatus = $this->insertCompanyTranslations();
            $status = $this->getFinalStatus($languageStatus, $translationsStatus);

            $statusRows[] = [$company->Name, $company->Id, $isWSLanguageEnabled, $languageStatus, $translationsStatus, $status];
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

    private function insertCompanyTranslations(): string
    {
        CompanyTranslation::query()->truncate();
        $translationsStatus = 'Truncated';
        $insertedCompanyTranslation = false;
        $companyLanguages = $this->companyLanguageRepository->all();

        foreach ($companyLanguages as $companyLanguage) {
            $officeLanguage = $this->officeLanguages->where('Code', $companyLanguage->Code)->first();

            if ($officeLanguage) {
                $translations = $this->officeTranslations->where('LanguageId', '=', $officeLanguage->Id)->all();

                foreach ($translations as $translation) {
                    // Insert Language Into CompanyTranslation
                    $insertedCompanyTranslation = $this->companyTranslationRepository->firstOrCreate([
                        'CompanyLanguageId' => $companyLanguage->Id,
                        'Type' => $translation->Type,
                        'ElementName' => $translation->ElementName
                    ], [
                        'Translations' => $translation->Translations
                    ]);
                }
            }
        }

        return $insertedCompanyTranslation ? 'Successful' : $translationsStatus;
    }

    private function getFinalStatus($languageStatus, $translationsStatus): string
    {
        if (($languageStatus === 'No Changes' && $translationsStatus === 'Successful') ||
            ($languageStatus === 'Successful' && $translationsStatus === 'No Changes')) {
            $status = 'Partially Success';
        } elseif ($languageStatus === 'Successful' && $translationsStatus === 'Successful') {
            $status = 'Successful';
        }

        return $status ?? 'Failure';
    }

}
