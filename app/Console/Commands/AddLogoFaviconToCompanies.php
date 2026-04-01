<?php

namespace App\Console\Commands;

use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AddLogoFaviconToCompanies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Examples:
     *   php artisan script:add-company-logo-favicon --companyId=821 --skipCompanyId=943
     *   php artisan script:add-company-logo-favicon -C 821 -S 943
     */
    protected $signature = 'script:add-company-logo-favicon
                           {--C|companyId=* : Specific company IDs to process (can be used multiple times)}
                           {--S|skipCompanyId=* : Company IDs to skip (can be used multiple times)}';

    /**
     * The console command description.
     */
    protected $description = 'Add Company Logo, Inverted Logo and Favicon into Company table.';

    public function __construct(
        protected CompanyRepositoryInterface $companyRepository
    )
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    /**
     * For Each Company in the system
     *      get company logo, inverted logo and favicon URL and validate if the URLs are accessible
     *      save the URLs in the Company table if they are accessible
     *
     */
    public function handle(): int
    {
        try {
            $companies = $this->getCompanies();
            if ($companies->isEmpty()) {
                $this->warn('No companies found matching the criteria.');
                return Command::SUCCESS;
            }
            $this->info("Processing {$companies->count()} companies...");

            $results = $this->processCompanies($companies);
            $this->displayResults($results);

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error("Command failed: {$e->getMessage()}");
            Log::error('AddLogoFaviconToCompanies command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Get companies to process based on module and filters.
     */
    private function getCompanies(): Collection
    {
        $attributes = [
            ['column' => 'Disabled', 'operand' => '=', 'value' => 0],
        ];

        // Filter by specific company IDs if provided
        if ($inputCompanyIds = $this->option('companyId')) {
            $attributes[] = ['column' => 'Id', 'operand' => '=', 'value' => $inputCompanyIds];
        }

        // Exclude specific company IDs if provided
        if ($ignoredCompanyIds = $this->option('skipCompanyId')) {
            $attributes[] = ['column' => 'Id', 'operand' => '!=', 'value' => $ignoredCompanyIds];
        }

        return $this->companyRepository->getByAttributes(
            $attributes, ['imageHostAccount'], ['Id', 'Name', 'Logo', 'InvertedLogo', 'Favicon']
        );
    }

    /**
     * Process all companies with progress tracking.
     */
    private function processCompanies(Collection $companies): SupportCollection
    {
        $bar = $this->output->createProgressBar($companies->count());
        $bar->setFormat('verbose');
        $bar->start();

        $results = $companies->map(function ($company) use ($bar) {
            $result = $this->processCompany($company);
            $bar->advance();
            return $result;
        });

        $bar->finish();
        $this->newLine();

        return $results;
    }

    /**
     * Process a single company.
     */
    private function processCompany(Model $company): array
    {
        try {
            if (!$company->imageHostAccount) {
                return [$company->Id, $company->Name, 0, 'No imageHostAccount Found'];
            }
            $company->Logo = $company->Logo ?: $this->getValidatedCompanyMediaUrl("{$company->imageHostAccount->Home}/logo.png");
            $company->InvertedLogo = $company->InvertedLogo ?: $this->getValidatedCompanyMediaUrl("{$company->imageHostAccount->Home}/inverted_logo.png");
            $company->Favicon = $company->Favicon ?: $this->getValidatedCompanyMediaUrl("{$company->imageHostAccount->Home}/favicon.ico");

            $columnCount = 0;
            if ($company->isDirty(['Logo', 'InvertedLogo', 'Favicon'])) {
                $columnCount = count($company->getDirty());
                $company->save();
            }

            $columnCount = $company->wasChanged() ? $columnCount : 0;
            $status = $columnCount > 0 ? 'Successful' : 'No Changes Needed';
            return [$company->Id, $company->Name, $columnCount, $status];

        } catch (Exception $e) {
            Log::error("Error processing company {$company->Id}: {$e->getMessage()}", [
                'company_id' => $company->Id,
                'company_name' => $company->Name,
                'error' => $e->getMessage()
            ]);
            return [$company->Id, $company->Name, 0, 'Error: ' . $e->getMessage()];
        }
    }

    private function getValidatedCompanyMediaUrl($url): ?string
    {
        try {
            $response = Http::timeout(10)->head($url);

            if ($response->successful()) {
                return $url;
            } else {
                return null;
            }
        } catch (Exception $e) {
            Log::error("Error accessing URL {$url}: {$e->getMessage()}", [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Display processing results in a table.
     */
    private function displayResults(SupportCollection $results): void
    {
        $headers = ['Company ID', 'Company Name', 'Records Updated', 'Status'];

        $this->newLine();
        $this->table($headers, $results->toArray());

        $totalCreated = $results->sum(fn($row) => is_numeric($row[2]) ? $row[2] : 0);
        $successfulCompanies = $results->where(3, 'Successful')->count();

        $this->newLine();
        $this->info("Summary:");
        $this->info("- Companies processed: {$results->count()}");
        $this->info("- Companies with changes: {$successfulCompanies}");
        $this->info("- Total records Updated: {$totalCreated}");
    }

}
