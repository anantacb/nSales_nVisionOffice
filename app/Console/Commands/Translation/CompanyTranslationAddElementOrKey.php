<?php

namespace App\Console\Commands\Translation;

use App\Repositories\Eloquent\Company\CompanyLanguage\CompanyLanguageRepositoryInterface;
use App\Repositories\Eloquent\Company\CompanyTranslation\CompanyTranslationRepositoryInterface;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\Language\LanguageRepositoryInterface;
use App\Repositories\Eloquent\Office\Translation\TranslationRepositoryInterface;
use App\Services\Company\CompanyService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class CompanyTranslationAddElementOrKey extends Command
{
    /**
     * The name and signature of the console command.
     * The console command description.
     *
     *   php artisan script:translation:add-element-or-key
     *   php artisan script:translation:add-element-or-key -C821 -S943
     *   php artisan script:translation:add-element-or-key --companyId=821 --skipCompanyId=943
     * @var string
     */
    protected $signature = 'script:company-translation:add-element-or-key {--C|companyId=}';
    protected $description = 'Add missing element or keys in CompanyTranslation for selected companies.';

    protected CompanyRepositoryInterface $companyRepository;
    protected LanguageRepositoryInterface $languageRepository;
    protected TranslationRepositoryInterface $translationRepository;
    protected CompanyLanguageRepositoryInterface $companyLanguageRepository;
    protected CompanyTranslationRepositoryInterface $companyTranslationRepository;
    protected Collection $officeLanguages;
    protected Collection $officeTranslations;

    public function __construct(
        CompanyRepositoryInterface            $companyRepository,
        LanguageRepositoryInterface           $languageRepository,
        TranslationRepositoryInterface        $translationRepository,
        CompanyLanguageRepositoryInterface    $companyLanguageRepository,
        CompanyTranslationRepositoryInterface $companyTranslationRepository
    )
    {
        parent::__construct();
        $this->companyRepository = $companyRepository;
        $this->languageRepository = $languageRepository;
        $this->translationRepository = $translationRepository;
        $this->companyLanguageRepository = $companyLanguageRepository;
        $this->companyTranslationRepository = $companyTranslationRepository;
    }

    /**
     * For Company
     */
    /**
     *      get all translations for Default Language
     *      If any Key is missing in Other Company Translation Element
     *          add the Key from Default Company Translation and Update it into Target Language Company Translation
     *       If any Element is missing in Company Translation
     *           Insert Element into CompanyTranslation table from Company Translation
     */
    public function handle()
    {
        $this->processCompany();
        return Command::SUCCESS;
    }

    private function processCompany(): void
    {
        CompanyService::setCompanyDatabaseConnection($this->option('companyId'));
        $companyLanguages = $this->companyLanguageRepository->all();
        $defaultLanguage = $companyLanguages->where('IsDefault', 1)->first();
        $companyTranslations = $this->companyTranslationRepository->all();
        $defaultLanguageTranslations = $companyTranslations->where('CompanyLanguageId', $defaultLanguage->Id);

        foreach ($companyLanguages as $companyLanguage) {
            if ($companyLanguage->IsDefault) {
                continue;
            }
            foreach ($defaultLanguageTranslations as $defaultLanguageTranslation) {
                $this->processTranslation($companyLanguage, $defaultLanguageTranslation, $companyTranslations);
            }
        }
    }

    private function processTranslation($companyLanguage, $defaultTranslation, $companyTranslations): void
    {
        $companyTranslation = $companyTranslations->where('CompanyLanguageId', $companyLanguage->Id)
            ->where('ElementName', $defaultTranslation->ElementName)
            ->where('Type', $defaultTranslation->Type)
            ->first();
        if ($companyTranslation) {
            $this->updateExistingTranslation($companyLanguage, $companyTranslation, $defaultTranslation);
        } else {
            $this->createNewTranslation($companyLanguage, $defaultTranslation);
        }
    }

    private function updateExistingTranslation($companyLanguage, $companyTranslation, $defaultTranslation): void
    {
        $isUpdateRequired = false;
        $currentCompanyTranslations = $companyTranslation->Translations;

        foreach ($defaultTranslation->Translations as $key => $translationValue) {
            if (!array_key_exists($key, $currentCompanyTranslations)) {
                $currentCompanyTranslations[$key] = getTranslation($translationValue, $companyLanguage->Code);
                $isUpdateRequired = true;
            }
        }

        if ($isUpdateRequired) {
            try {
                $companyTranslation->Translations = $currentCompanyTranslations;
                $companyTranslation->save();
            } catch (Exception $e) {
                Log::error("Error updating translation for company language {$companyTranslation->CompanyLanguageId}: " . $e->getMessage());
            }
        }
    }

    private function createNewTranslation($companyLanguage, $defaultTranslation): void
    {
        $translations = [];
        foreach ($defaultTranslation->Translations as $key => $translationValue) {
            $translations[$key] = getTranslation($translationValue, $companyLanguage->Code);
        }
        try {
            $this->companyTranslationRepository->create([
                'CompanyLanguageId' => $companyLanguage->Id,
                'Type' => $defaultTranslation->Type,
                'ElementName' => $defaultTranslation->ElementName,
                'Translations' => $translations
            ]);
        } catch (Exception $e) {
            Log::error("Error creating new translation for company language {$companyLanguage->Id}: " . $e->getMessage());
        }
    }
}
