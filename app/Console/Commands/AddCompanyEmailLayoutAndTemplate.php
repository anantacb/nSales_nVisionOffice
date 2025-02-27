<?php

namespace App\Console\Commands;

use App\Repositories\Eloquent\Company\CompanyEmailLayout\CompanyEmailLayoutRepositoryInterface;
use App\Repositories\Eloquent\Company\CompanyEmailTemplate\CompanyEmailTemplateRepositoryInterface;
use App\Repositories\Eloquent\Company\CompanyLanguage\CompanyLanguageRepositoryInterface;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\EmailLayout\EmailLayoutRepositoryInterface;
use App\Repositories\Eloquent\Office\EmailTemplate\EmailTemplateRepositoryInterface;
use App\Repositories\Eloquent\Office\Language\LanguageRepositoryInterface;
use App\Services\Company\CompanyService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AddCompanyEmailLayoutAndTemplate extends Command
{
    /**
     * The name and signature of the console command.
     * The console command description.
     *
     *   php artisan script:add-company-email-layout-template
     *   php artisan script:add-company-email-layout-template -C821 -S943
     *   php artisan script:add-company-email-layout-template --companyId=821 --skipCompanyId=943
     * @var string
     */
    protected $signature = 'script:add-company-email-layout-template {--C|companyId=*} {--S|skipCompanyId=*}';
    protected $description = 'Add missing layout and template in CompanyEmailLayout, CompanyEmailTemplate for all companies or selected companies.';

    protected CompanyRepositoryInterface $companyRepository;
    protected LanguageRepositoryInterface $languageRepository;
    protected EmailLayoutRepositoryInterface $emailLayoutRepository;
    protected EmailTemplateRepositoryInterface $emailTemplateRepository;
    protected CompanyLanguageRepositoryInterface $companyLanguageRepository;
    protected CompanyEmailLayoutRepositoryInterface $companyEmailLayoutRepository;
    protected CompanyEmailTemplateRepositoryInterface $companyEmailTemplateRepository;
    protected Collection $officeLanguages;
    protected Collection $officeEmailLayouts;
    protected Collection $officeEmailTemplates;

    public function __construct(
        CompanyRepositoryInterface              $companyRepository,
        LanguageRepositoryInterface             $languageRepository,
        EmailLayoutRepositoryInterface          $emailLayoutRepository,
        EmailTemplateRepositoryInterface        $emailTemplateRepository,
        CompanyLanguageRepositoryInterface      $companyLanguageRepository,
        CompanyEmailLayoutRepositoryInterface   $companyEmailLayoutRepository,
        CompanyEmailTemplateRepositoryInterface $companyEmailTemplateRepository
    )
    {
        parent::__construct();
        $this->companyRepository = $companyRepository;
        $this->languageRepository = $languageRepository;
        $this->emailLayoutRepository = $emailLayoutRepository;
        $this->emailTemplateRepository = $emailTemplateRepository;
        $this->companyLanguageRepository = $companyLanguageRepository;
        $this->companyEmailLayoutRepository = $companyEmailLayoutRepository;
        $this->companyEmailTemplateRepository = $companyEmailTemplateRepository;
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
        $this->officeEmailLayouts = $this->emailLayoutRepository->all();
        $this->officeEmailTemplates = $this->emailTemplateRepository->all();
    }

    private function processCompany($company): array
    {
        CompanyService::setCompanyDatabaseConnection($company->Id);

        $companyLanguages = $this->companyLanguageRepository->all();
        $companyEmailLayouts = $this->companyEmailLayoutRepository->all();
        $companyEmailTemplates = $this->companyEmailTemplateRepository->all();

        $counters = [
            'companyEmailLayoutCounter' => 0,
            'companyEmailTemplateCounter' => 0
        ];

        foreach ($companyLanguages as $companyLanguage) {
            $officeLanguage = $this->officeLanguages->where('Code', $companyLanguage->Code)->first();
            if (!$officeLanguage) {
                Log::warning("No matching office language found for company language {$companyLanguage->Code} in company {$company->Id}");
                continue;
            }
            $officeEmailLayouts = $this->officeEmailLayouts->where('LanguageId', $officeLanguage->Id);
            $officeEmailTemplates = $this->officeEmailTemplates->where('LanguageId', $officeLanguage->Id);

            foreach ($officeEmailLayouts as $officeEmailLayout) {
                $this->processCompanyEmailLayout($companyLanguage, $officeEmailLayout, $companyEmailLayouts, $officeEmailTemplates, $companyEmailTemplates, $counters);
            }
        }

        $status = $this->getFinalStatus($counters['companyEmailLayoutCounter'], $counters['companyEmailTemplateCounter']);
        return [$company->Name, $company->Id, $counters['companyEmailLayoutCounter'], $counters['companyEmailTemplateCounter'], $status];
    }

    private function processCompanyEmailLayout($companyLanguage, $officeEmailLayout, $companyEmailLayouts, $officeEmailTemplates, $companyEmailTemplates, &$counters): void
    {
        $companyEmailLayout = $companyEmailLayouts->where('LanguageId', $companyLanguage->Id)
            ->where('Name', $officeEmailLayout->Name)
            ->first();

        if (!$companyEmailLayout) {
            $companyEmailLayout = $this->createCompanyEmailLayout($companyLanguage, $officeEmailLayout);
            $counters['companyEmailLayoutCounter']++;
        }

        if ($companyEmailLayout) {
            $this->processCompanyEmailTemplates($companyLanguage, $officeEmailLayout, $companyEmailLayout, $officeEmailTemplates, $companyEmailTemplates, $counters);
        }
    }

    private function createCompanyEmailLayout($companyLanguage, $officeEmailLayout): ?Model
    {
        try {
            return $this->companyEmailLayoutRepository->create([
                'LanguageId' => $companyLanguage->Id,
                'Name' => $officeEmailLayout->Name,
                'Template' => $officeEmailLayout->Template,
            ]);
        } catch (Exception $e) {
            Log::error("Error creating new Layout for company language {$companyLanguage->Id}, layout {$officeEmailLayout->Name}: " . $e->getMessage());
            return null;
        }
    }

    private function processCompanyEmailTemplates($companyLanguage, $officeEmailLayout, $companyEmailLayout, $officeEmailTemplates, $companyEmailTemplates, &$counters): void
    {
        $officeEmailTemplates = $officeEmailTemplates->where('LayoutId', $officeEmailLayout->Id);

        foreach ($officeEmailTemplates as $officeEmailTemplate) {

            $companyEmailTemplate = $companyEmailTemplates
                //->where('LayoutId', $companyEmailLayout->Id)
                ->where('ElementName', $officeEmailTemplate->ElementName)
                ->first();

            if (!$companyEmailTemplate) {
                $this->createCompanyEmailTemplate($companyLanguage, $officeEmailTemplate, $companyEmailLayout);
                $counters['companyEmailTemplateCounter']++;
            }
        }
    }

    private function createCompanyEmailTemplate($companyLanguage, $officeEmailTemplate, $companyEmailLayout): void
    {
        try {
            $this->companyEmailTemplateRepository->create([
                'LanguageId' => $companyLanguage->Id,
                'LayoutId' => $companyEmailLayout->Id,
                'ElementName' => $officeEmailTemplate->ElementName,
                'Subject' => $officeEmailTemplate->Subject,
                'Template' => $officeEmailTemplate->Template,
            ]);
        } catch (Exception $e) {
            Log::error(
                "Error creating new Template for company language {$companyLanguage->Id}, layout {$companyEmailLayout->Name}, {$officeEmailTemplate->ElementName}: " .
                $e->getMessage()
            );
        }
    }

    private function getFinalStatus($companyEmailLayoutCounter, $companyEmailTemplateCounter): string
    {
        if ($companyEmailLayoutCounter > 0 && $companyEmailTemplateCounter > 0) {
            return 'Successful';
        } elseif ($companyEmailLayoutCounter > 0 || $companyEmailTemplateCounter > 0) {
            return 'Partially Success';
        }
        return 'No Changes';
    }

    private function displayResults($statusRows): void
    {
        $headers = ['Name', 'Id', 'Layouts', 'Templates', 'Status'];
        $this->table($headers, $statusRows);
    }
}
