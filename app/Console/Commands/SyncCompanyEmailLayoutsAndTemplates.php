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

class SyncCompanyEmailLayoutsAndTemplates extends Command
{
    /**
     * The name and signature of the console command.
     * The console command description.
     *
     *   php artisan script:sync-company-email-layouts-templates
     *   php artisan script:sync-company-email-layouts-templates -C821 -S943
     *   php artisan script:sync-company-email-layouts-templates --companyId=821 --skipCompanyId=943
     * @var string
     */
    protected $signature = 'script:sync-company-email-layouts-templates
                            {--C|companyId=* : Specific company IDs to process}
                            {--S|skipCompanyId=* : Company IDs to skip}';

    protected $description = 'Synchronize missing email layouts and templates for companies.';

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
     * Get all Company Languages, Layouts and Templates
     * For Each Company Language
     *      get all Company layouts for Language
     *      For Each Company Layout
     *          If any layout is missing in Company layout
     *              Insert layout into CompanyEmailLayout table from Office EmailLayout
     *          get all Company templates for Language
     *          For Each Company Templates
     *              If any layout is missing in Company Template
     *              Insert layout into CompanyEmailTemplate table from Office EmailTemplate
     */
    public function handle(): int
    {
        $companies = $this->getCompanies();
        $this->loadOfficeData();

        $this->info("Processing {$companies->count()} companies...");
        $bar = $this->output->createProgressBar($companies->count());
        $bar->start();

        $results = $companies->map(fn($company) => $this->processCompany($company, $bar))->all();

        $bar->finish();
        $this->newLine();
        $this->displayResults($results);

        return self::SUCCESS;
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

    private function processCompany(Model $company, $bar): array
    {
        try {
            CompanyService::setCompanyDatabaseConnection($company->Id);

            $companyLanguages = $this->companyLanguageRepository->all();
            $companyLayouts = $this->companyEmailLayoutRepository->all();
            $companyTemplates = $this->companyEmailTemplateRepository->all();
            $counters = ['layouts' => 0, 'templates' => 0];

            $this->syncCompanyData($companyLanguages, $companyLayouts, $companyTemplates, $counters);
            $status = $this->getFinalStatus($counters['layouts'], $counters['templates']);

            return [$company->Name, $company->Id, $counters['layouts'], $counters['templates'], $status];
        } catch (Exception $e) {
            Log::error("Failed to process company {$company->Id}: {$e->getMessage()}");
            return [$company->Name, $company->Id, 0, 0, "Error: {$e->getMessage()}"];
        } finally {
            $bar->advance();
        }
    }

    private function syncCompanyData(Collection $companyLanguages, Collection $companyLayouts, Collection $companyTemplates, array &$counters): void
    {
        foreach ($companyLanguages as $companyLanguage) {
            $officeLanguage = $this->officeLanguages->firstWhere('Code', $companyLanguage->Code);
            if (!$officeLanguage) {
                Log::warning("No office language for code {$companyLanguage->Code}");
                continue;
            }

            $officeLayouts = $this->officeEmailLayouts->where('LanguageId', $officeLanguage->Id);
            $officeTemplates = $this->officeEmailTemplates->where('LanguageId', $officeLanguage->Id);

            foreach ($officeLayouts as $officeLayout) {
                $companyLayout = $this->syncCompanyLayout($companyLanguage, $officeLayout, $companyLayouts, $counters);

                if ($companyLayout) {
                    $this->syncCompanyTemplates($companyLanguage, $officeLayout, $companyLayout, $officeTemplates, $companyTemplates, $counters);
                }
            }
        }
    }

    private function syncCompanyLayout(Model $companyLanguage, Model $officeLayout, Collection $companyLayouts, array &$counters): ?Model
    {
        $companyLayout = $companyLayouts->firstWhere(
            fn($item) => $item->LanguageId === $companyLanguage->Id && $item->Name === $officeLayout->Name
        );

        if (!$companyLayout) {
            $companyLayout = $this->createCompanyEmailLayout($companyLanguage, $officeLayout);
            if ($companyLayout) {
                $counters['layouts']++;
            }
        }
        return $companyLayout;
    }

    private function createCompanyEmailLayout(Model $companyLanguage, Model $officeLayout): ?Model
    {
        try {
            return $this->companyEmailLayoutRepository->create([
                'LanguageId' => $companyLanguage->Id,
                'Name' => $officeLayout->Name,
                'Template' => $officeLayout->Template,
            ]);
        } catch (Exception $e) {
            Log::error("Failed to create layout for language {$companyLanguage->Id}, layout {$officeLayout->Name}: {$e->getMessage()}");
            return null;
        }
    }

    private function syncCompanyTemplates(Model $companyLanguage, Model $officeLayout, Model $companyLayout, Collection $officeTemplates, Collection $companyTemplates, array &$counters): void
    {
        $relevantTemplates = $officeTemplates->where('LayoutId', $officeLayout->Id);

        foreach ($relevantTemplates as $officeTemplate) {
            $companyEmailTemplate = $companyTemplates->firstWhere(
                fn($item) => $item->LanguageId === $companyLanguage->Id && $item->ElementName === $officeTemplate->ElementName
            );

            if (!$companyEmailTemplate) {
                if ($this->createCompanyEmailTemplate($companyLanguage, $officeTemplate, $companyLayout)) {
                    $counters['templates']++;
                }
            }
        }
    }

    private function createCompanyEmailTemplate(Model $companyLanguage, Model $officeTemplate, Model $companyLayout): bool
    {
        try {
            $this->companyEmailTemplateRepository->create([
                'LanguageId' => $companyLanguage->Id,
                'LayoutId' => $companyLayout->Id,
                'ElementName' => $officeTemplate->ElementName,
                'Subject' => $officeTemplate->Subject,
                'Template' => $officeTemplate->Template,
            ]);
            return true;
        } catch (Exception $e) {
            Log::error("Failed to create template for layout {$companyLayout->Id}, template $officeTemplate->ElementName: {$e->getMessage()}");
            return false;
        }
    }

    private function getFinalStatus(int $layoutCount, int $templateCount): string
    {
        return match (true) {
            $layoutCount > 0 && $templateCount > 0 => 'Successful',
            $layoutCount > 0 || $templateCount > 0 => 'Partially Successful',
            default => 'No Changes',
        };
    }

    private function displayResults(array $results): void
    {
        $this->table(['Name', 'Id', 'Layouts', 'Templates', 'Status'], $results);
    }
}
