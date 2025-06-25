<?php

namespace App\Console\Commands;

use App\Models\Office\Table;
use App\Repositories\Eloquent\Company\CompanyLanguage\CompanyLanguageRepositoryInterface;
use App\Repositories\Eloquent\Company\WebShopText\WebShopTextRepositoryInterface;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyModule\CompanyModuleRepositoryInterface;
use App\Repositories\Eloquent\Office\Module\ModuleRepositoryInterface;
use App\Services\Company\CompanyService;
use App\Services\Traits\ModuleHelperTrait;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AddWebShopTextToCompanies extends Command
{
    use ModuleHelperTrait;

    /**
     * The name and signature of the console command.
     *
     * Examples:
     *   php artisan script:add-webshoptext --moduleName=Campaign --tableName=Campaign --fieldName=Number --companyId=821 --skipCompanyId=943
     *   php artisan script:add-webshoptext -M Campaign -T Campaign -F Number -C 821 -S 943
     */
    protected $signature = 'script:add-webshoptext
                           {--M|moduleName= : The module name to process}
                           {--T|tableName= : The table name within the module}
                           {--F|fieldName= : The column name to use as element number}
                           {--C|companyId=* : Specific company IDs to process (can be used multiple times)}
                           {--S|skipCompanyId=* : Company IDs to skip (can be used multiple times)}';

    /**
     * The console command description.
     */
    protected $description = 'Insert WebShopText entries for all companies or selected companies based on module data.';

    protected ?Model $module = null;
    protected string $table = '';
    protected string $column = '';

    public function __construct(
        protected ModuleRepositoryInterface          $moduleRepository,
        protected CompanyModuleRepositoryInterface   $companyModuleRepository,
        protected CompanyRepositoryInterface         $companyRepository,
        protected CompanyLanguageRepositoryInterface $companyLanguageRepository,
        protected WebShopTextRepositoryInterface     $webShopTextRepository
    )
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    /**
     * For Each Company Language
     *      get all data from the specified table
     *     For Each Element in the table
     *         For Each Company Language
     *        create or update WebShopText for each section type (Header, SubHeader, Body, Footer)
     *        If any WebShopText is created, increment the element counter
     */
    public function handle(): int
    {
        try {
            $this->validateInputs();

            $companies = $this->getCompanies();
            if ($companies->isEmpty()) {
                $this->warn('No companies found matching the criteria.');
                return Command::SUCCESS;
            }

            $this->setTableInfo();

            if (empty($this->table) || empty($this->column)) {
                $this->error('Invalid table or column specified.');
                return Command::FAILURE;
            }

            $this->info("Processing {$companies->count()} companies...");

            $results = $this->processCompanies($companies);
            $this->displayResults($results);

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error("Command failed: {$e->getMessage()}");
            Log::error('AddWebShopTextToCompanies command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Validate required input options.
     */
    private function validateInputs(): void
    {
        $required = ['moduleName', 'tableName', 'fieldName'];

        foreach ($required as $option) {
            if (empty($this->option($option))) {
                throw new Exception("The --{$option} option is required.");
            }
        }
    }

    /**
     * Get companies to process based on module and filters.
     */
    private function getCompanies(): Collection
    {
        $this->module = $this->moduleRepository->firstByAttributes([
            ['column' => 'Name', 'operand' => '=', 'value' => $this->option('moduleName')],
            ['column' => 'Type', 'operand' => '=', 'value' => ['Standard', 'Extension']],
        ], [], ['Id', 'Name', 'MainTableName']);

        if (!$this->module) {
            throw new Exception("Module '{$this->option('moduleName')}' not found.");
        }

        $moduleCompanyIds = $this->companyModuleRepository->getByAttributes([
            ['column' => 'ModuleId', 'operand' => '=', 'value' => $this->module->Id]
        ], [], ['CompanyId'])->pluck('CompanyId')->toArray();

        if (empty($moduleCompanyIds)) {
            return new Collection();
        }

        $attributes = [
            ['column' => 'Disabled', 'operand' => '=', 'value' => 0],
            ['column' => 'Id', 'operand' => '=', 'value' => $moduleCompanyIds]
        ];

        // Filter by specific company IDs if provided
        if ($inputCompanyIds = $this->option('companyId')) {
            $validCompanyIds = array_intersect($moduleCompanyIds, $inputCompanyIds);
            if (empty($validCompanyIds)) {
                return new Collection();
            }
            $attributes[] = ['column' => 'Id', 'operand' => '=', 'value' => $validCompanyIds];
        }

        // Exclude specific company IDs if provided
        if ($ignoredCompanyIds = $this->option('skipCompanyId')) {
            $attributes[] = ['column' => 'Id', 'operand' => '!=', 'value' => $ignoredCompanyIds];
        }

        return $this->companyRepository->getByAttributes($attributes, [], ['Id', 'Name']);
    }

    /**
     * Validate and set table information.
     */
    private function setTableInfo(): void
    {
        $tableName = $this->option('tableName');

        $tableExists = Table::where('ModuleId', $this->module->Id)
            ->whereIn('Type', ['Server', 'Both'])
            ->where('Name', $tableName)
            ->exists();

        if (!$tableExists) {
            throw new Exception("Table '{$tableName}' does not exist in module '{$this->module->Name}'.");
        }

        $this->table = $tableName;
        $this->column = $this->option('fieldName');
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
            CompanyService::setCompanyDatabaseConnection($company->Id);

            $companyLanguages = $this->companyLanguageRepository->all(['Code']);
            if ($companyLanguages->isEmpty()) {
                return [$company->Id, $company->Name, 0, 'No languages found'];
            }

            $elementData = $this->getElementData();
            if ($elementData->isEmpty()) {
                return [$company->Id, $company->Name, 0, 'No data found'];
            }

            $createdCount = $this->processElementData($elementData, $companyLanguages);
            $status = $createdCount > 0 ? 'Successful' : 'No changes needed';

            return [$company->Id, $company->Name, $createdCount, $status];

        } catch (Exception $e) {
            Log::error("Error processing company {$company->Id}: {$e->getMessage()}", [
                'company_id' => $company->Id,
                'company_name' => $company->Name,
                'error' => $e->getMessage()
            ]);
            return [$company->Id, $company->Name, 0, 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Get element data from the company database.
     */
    private function getElementData(): SupportCollection
    {
        return DB::connection('mysql_company')
            ->table($this->table)
            ->where('Disabled', 0)
            ->select([$this->column])
            ->get();
    }

    /**
     * Process element data for all languages.
     */
    private function processElementData(SupportCollection $elementData, Collection $companyLanguages): int
    {
        $createdCount = 0;
        $sectionTypes = ['Header', 'SubHeader', 'Body', 'Footer'];

        foreach ($elementData as $element) {
            $elementNumber = $element->{$this->column};

            foreach ($companyLanguages as $language) {
                foreach ($sectionTypes as $sectionType) {
                    $wasRecentlyCreated = $this->createWebShopText($elementNumber, $sectionType, $language->Code);
                    if ($wasRecentlyCreated) {
                        $createdCount++;
                    }
                }
            }
        }

        return $createdCount;
    }

    /**
     * Create or find existing WebShopText entry.
     */
    private function createWebShopText(mixed $elementNumber, string $sectionType, string $languageCode): bool
    {
        $webShopText = $this->webShopTextRepository->firstOrCreate(
            [
                'ElementType' => $this->table,
                'ElementNumber' => $elementNumber,
                'Type' => $sectionType,
                'Language' => $languageCode,
                'CustomerColumn' => null,
                'CustomerColumnValue' => null,
            ],
            [
                'Text' => '',
            ]
        );

        return $webShopText->wasRecentlyCreated;
    }

    /**
     * Display processing results in a table.
     */
    private function displayResults(SupportCollection $results): void
    {
        $headers = ['Company ID', 'Company Name', 'Records Created', 'Status'];

        $this->newLine();
        $this->table($headers, $results->toArray());

        $totalCreated = $results->sum(fn($row) => is_numeric($row[2]) ? $row[2] : 0);
        $successfulCompanies = $results->where(3, 'Successful')->count();

        $this->newLine();
        $this->info("Summary:");
        $this->info("- Companies processed: {$results->count()}");
        $this->info("- Companies with changes: {$successfulCompanies}");
        $this->info("- Total records created: {$totalCreated}");
    }
}
