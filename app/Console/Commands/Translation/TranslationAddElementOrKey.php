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

class TranslationAddElementOrKey extends Command
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
    protected $signature = 'script:translation:add-element-or-key {--C|companyId=*} {--S|skipCompanyId=*}';
    protected $description = 'Add missing element or keys in CompanyTranslation for all companies or selected companies.';

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
     * For Each Company
     */
    /**
     * Get all Company Languages and Translations
     * For Each Company Language
     *      get all translations for Language
     *      If any Key is missing in Company Translation Element
     *          add the Key from Office Translation and Update it into CompanyTranslation
     *       If any Element is missing in Company Translation
     *           Insert Element into CompanyTranslation table from Office Translation
     */
    public function handle()
    {
        $companies = $this->getCompanies();
        $this->loadOfficeData();

        $bar = $this->output->createProgressBar($companies->count());
        $bar->start();

        $statusRows = $companies->map(function ($company) use ($bar) {
            try {
                $result = $this->processCompany($company);
            } catch (Exception $e) {
                Log::error("Error processing company {$company->Id}: " . $e->getMessage());
                $result = [$company->Name, $company->Id, 0, 0, 'Error: ' . $e->getMessage()];
            }
            $bar->advance();
            return $result;
        });

        $bar->finish();
        $this->info(PHP_EOL);
        $this->displayResults($statusRows);

        return Command::SUCCESS;
    }

    private function getCompanies(): Collection
    {
        $attributes = [['column' => 'Disabled', 'operand' => '=', 'value' => '0']];

        if ($inputCompanyIds = $this->option('companyId')) {
            $attributes[] = ['column' => 'Id', 'operand' => '=', 'value' => $inputCompanyIds];
        }

        if ($ignoredCompanyIds = $this->option('skipCompanyId')) {
            $attributes[] = ['column' => 'Id', 'operand' => '!=', 'value' => $ignoredCompanyIds];
        }

        return $this->companyRepository->getByAttributes($attributes);
    }

    private function loadOfficeData(): void
    {
        $this->officeLanguages = $this->languageRepository->all();
        $this->officeTranslations = $this->translationRepository->all();
    }

    private function processCompany($company): array
    {
        CompanyService::setCompanyDatabaseConnection($company->Id);

        $companyLanguages = $this->companyLanguageRepository->all();
        $companyTranslations = $this->companyTranslationRepository->all();

        $counters = [
            'elementCounter' => 0,
            'keyCounter' => 0
        ];

        foreach ($companyLanguages as $companyLanguage) {
            $officeLanguage = $this->officeLanguages->where('Code', $companyLanguage->Code)->first();
            if (!$officeLanguage) {
                Log::warning("No matching office language found for company language {$companyLanguage->Code} in company {$company->Id}");
                continue;
            }
            $officeTranslations = $this->officeTranslations->where('LanguageId', $officeLanguage->Id);

            foreach ($officeTranslations as $officeTranslation) {
                $this->processTranslation($companyLanguage, $officeTranslation, $companyTranslations, $counters);
            }
        }

        $status = $this->getFinalStatus($counters['elementCounter'], $counters['keyCounter']);
        return [$company->Name, $company->Id, $counters['elementCounter'], $counters['keyCounter'], $status];
    }

    private function processTranslation($companyLanguage, $officeTranslation, $companyTranslations, &$counters): void
    {
        $companyTranslation = $companyTranslations->where('CompanyLanguageId', $companyLanguage->Id)
            ->where('ElementName', $officeTranslation->ElementName)
            ->where('Type', $officeTranslation->Type)
            ->first();

        if ($companyTranslation) {
            $this->updateExistingTranslation($companyTranslation, $officeTranslation, $counters);
        } else {
            $this->createNewTranslation($companyLanguage, $officeTranslation);
            $counters['elementCounter']++;
        }
    }

    private function updateExistingTranslation($companyTranslation, $officeTranslation, &$counters): void
    {
        $isUpdateRequired = false;
        $currentCompanyTranslations = $companyTranslation->Translations;

        foreach ($officeTranslation->Translations as $key => $translationValue) {
            if (!array_key_exists($key, $currentCompanyTranslations)) {
                $currentCompanyTranslations[$key] = $translationValue;
                $isUpdateRequired = true;
            }
        }

        if ($isUpdateRequired) {
            try {
                $companyTranslation->Translations = $currentCompanyTranslations;
                $companyTranslation->save();
                $counters['keyCounter']++;
            } catch (Exception $e) {
                Log::error("Error updating translation for company language {$companyTranslation->CompanyLanguageId}: " . $e->getMessage());
            }
        }
    }

    private function createNewTranslation($companyLanguage, $officeTranslation): void
    {
        try {
            $this->companyTranslationRepository->create([
                'CompanyLanguageId' => $companyLanguage->Id,
                'Type' => $officeTranslation->Type,
                'ElementName' => $officeTranslation->ElementName,
                'Translations' => $officeTranslation->Translations
            ]);
        } catch (Exception $e) {
            Log::error("Error creating new translation for company language {$companyLanguage->Id}: " . $e->getMessage());
        }
    }

    private function getFinalStatus($elementCounter, $keyCounter): string
    {
        if ($elementCounter > 0 && $keyCounter > 0) {
            return 'Successful';
        } elseif ($elementCounter > 0 || $keyCounter > 0) {
            return 'Partially Success';
        }
        return 'No Changes';
    }

    private function displayResults($statusRows): void
    {
        $headers = ['Name', 'Id', 'Element', 'Key', 'Status'];
        $this->table($headers, $statusRows);
    }
}
