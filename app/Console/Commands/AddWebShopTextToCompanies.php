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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AddWebShopTextToCompanies extends Command
{
    use ModuleHelperTrait;

    /**
     * The name and signature of the console command.
     * The console command description.
     *
     *   php artisan script:add-webshoptext -MCampaign -TCampaign -FNumber -C821 -C821 -S943
     *   php artisan script:add-webshoptext --moduleName=Campaign --tableName=Campaign --fieldName=Number --companyId=821 --skipCompanyId=943
     * @var string
     */
    protected $signature = 'script:add-webshoptext {--M|moduleName=} {--T|tableName=} {--F|fieldName=} {--C|companyId=*} {--S|skipCompanyId=*}';
    protected $description = 'Insert data into table:WebShopText for all companies or selected companies.';

    protected Model $module;
    protected string $table;
    protected string $column;
    protected ModuleRepositoryInterface $moduleRepository;
    protected CompanyModuleRepositoryInterface $companyModuleRepository;
    protected CompanyRepositoryInterface $companyRepository;
    protected CompanyLanguageRepositoryInterface $companyLanguageRepository;
    protected WebShopTextRepositoryInterface $webShopTextRepository;

    public function __construct(
        ModuleRepositoryInterface          $moduleRepository,
        CompanyModuleRepositoryInterface   $companyModuleRepository,
        CompanyRepositoryInterface         $companyRepository,
        CompanyLanguageRepositoryInterface $companyLanguageRepository,
        WebShopTextRepositoryInterface     $webShopTextRepository
    )
    {
        parent::__construct();
        $this->moduleRepository = $moduleRepository;
        $this->companyModuleRepository = $companyModuleRepository;
        $this->companyRepository = $companyRepository;
        $this->companyLanguageRepository = $companyLanguageRepository;
        $this->webShopTextRepository = $webShopTextRepository;
    }

    /**
     * For Each Company
     */
    /**
     * For Each Company Language
     *      get all data from the specified table
     *     For Each Element in the table
     *         For Each Company Language
     *        create or update WebShopText for each section type (Header, SubHeader, Body, Footer)
     *        If any WebShopText is created, increment the element counter
     */
    public function handle()
    {
        $companies = $this->getCompanies();
        $this->setTableInfo();

        if (empty($this->table) || empty($this->column)) {
            $this->error("Invalid table or column specified.");
            return Command::FAILURE;
        }

        $bar = $this->output->createProgressBar($companies->count());
        $bar->start();

        $statusRows = $companies->map(function ($company) use ($bar) {
            try {
                $result = $this->processCompany($company);
            } catch (Exception $e) {
                Log::error("Error processing company {$company->Id}: " . $e->getMessage());
                $result = [$company->Id, $company->Name, 0, 'Error: ' . $e->getMessage()];
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
        $this->module = $this->moduleRepository->firstByAttributes([
            ['column' => 'Name', 'operand' => '=', 'value' => $this->option('moduleName')],
            ['column' => 'Type', 'operand' => '=', 'value' => ['Standard', 'Extension']],
        ], [], ['Id', 'Name', 'MainTableName']);

        $moduleCompanyIds = $this->companyModuleRepository->getByAttributes([
            ['column' => 'ModuleId', 'operand' => '=', 'value' => $this->module->Id]
        ], [], ['CompanyId'])->pluck('CompanyId')->toArray();

        $attributes = [
            ['column' => 'Disabled', 'operand' => '=', 'value' => '0'],
            ['column' => 'Id', 'operand' => '=', 'value' => $moduleCompanyIds]
        ];

        if ($inputCompanyIds = $this->option('companyId')) {
            $attributes[] = ['column' => 'Id', 'operand' => '=', 'value' => $inputCompanyIds];
        }

        if ($ignoredCompanyIds = $this->option('skipCompanyId')) {
            $attributes[] = ['column' => 'Id', 'operand' => '!=', 'value' => $ignoredCompanyIds];
        }

        return $this->companyRepository->getByAttributes($attributes);
    }

    private function setTableInfo()
    {
        $tables = Table::where('ModuleId', $this->module->Id)
            ->whereIn('Type', ['Server', 'Both'])
            ->select(['Name', 'Id'])->get();

        if ($tables->contains('Name', $this->option('tableName'))) {
            $this->table = $this->option('tableName');
            $this->column = $this->option('fieldName');
        } else {
            $errorMessage = "Table: {$this->option('tableName')} does not exist in module: {$this->module->Name}.";
            $this->error($errorMessage);
            Log::error($errorMessage);
        }
    }

    private function processCompany($company): array
    {
        CompanyService::setCompanyDatabaseConnection($company->Id);
        $companyLanguages = $this->companyLanguageRepository->all();
        $allElementData = DB::connection('mysql_company')->table($this->table)
            ->where('Disabled', 0)->get();

        $counters = [
            'elementCounter' => 0
        ];

        foreach ($allElementData as $elementData) {
            foreach ($companyLanguages as $companyLanguage) {
                $this->processWebShopText($companyLanguage, $elementData, $counters);
            }
        }

        $status = $this->getFinalStatus($counters['elementCounter']);
        return [$company->Id, $company->Name, $counters['elementCounter'], $status];
    }

    private function processWebShopText($companyLanguage, $elementData, &$counters): void
    {
        $sectionTypes = ['Header', 'SubHeader', 'Body', 'Footer'];

        foreach ($sectionTypes as $sectionType) {
            $webShopText = $this->webShopTextRepository->firstOrCreate(
                [
                    'ElementType' => $this->table,
                    'ElementNumber' => $elementData->{$this->column},
                    'Type' => $sectionType,
                    'Language' => $companyLanguage->Code,
                    'CustomerColumn' => null,
                    'CustomerColumnValue' => null,
                ],
                [
                    'Text' => '',
                ]
            );
            if ($webShopText->wasRecentlyCreated) {
                $counters['elementCounter']++;
            }

        }

    }

    private function getFinalStatus($elementCounter): string
    {
        if ($elementCounter > 0) {
            return 'Successful';
        }
        return 'No Changes';
    }

    private function displayResults($statusRows): void
    {
        $headers = ['Id', 'Name', 'Element', 'Status'];
        $this->table($headers, $statusRows);
    }
}
