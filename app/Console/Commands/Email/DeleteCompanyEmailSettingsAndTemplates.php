<?php

namespace App\Console\Commands\Email;

use App\Models\Office\Setting;
use App\Repositories\Eloquent\Company\CompanyEmailTemplate\CompanyEmailTemplateRepositoryInterface;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\EmailTemplate\EmailTemplateRepositoryInterface;
use App\Repositories\Eloquent\Office\Module\ModuleRepositoryInterface;
use App\Services\Company\CompanyService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class DeleteCompanyEmailSettingsAndTemplates extends Command
{
    private const MODULE_NAME = 'CompanyEmail';
    private const MODULE_KEYS = ['EmailEvents'];
    /**
     * The name and signature of the console command.
     * The console command description.
     *
     *   php artisan script:delete-company-email-setting-templates
     *   php artisan script:delete-company-email-setting-templates -C821 -S943
     *   php artisan script:delete-company-email-setting-templates -EABANDONED_CART_MAIL -Aboth -C821
     *   php artisan script:delete-company-email-setting-templates --companyId=821 --skipCompanyId=943
     * @var string
     */
    protected $signature = 'script:delete-company-email-setting-templates
                            {--E|elementName=* : Email template and settings element names to process (can be used multiple times)}
                            {--A|applyOn= : apply on setting, template or both (default: both)}
                            {--C|companyId=* : Specific company IDs to process}
                            {--S|skipCompanyId=* : Company IDs to skip}';
    protected $description = 'Delete specific email settings and templates for companies with the CompanyEmail module';

    private $elementName;
    private $applyOn;

    public function __construct(
        protected CompanyRepositoryInterface              $companyRepository,
        protected EmailTemplateRepositoryInterface        $emailTemplateRepository,
        protected ModuleRepositoryInterface               $moduleRepository,
        protected CompanyEmailTemplateRepositoryInterface $companyEmailTemplateRepository
    )
    {
        parent::__construct();
    }

    /**
     * For Each Company
     */
    /**
     * Get all Company Module and Templates
     * For Each Company
     *      update Company Module settings by removing the specific element from settings value
     *      remove all Company templates with the specific element name
     */
    public function handle(): int
    {
        $this->setInputOptions();
        $module = $this->getModuleWithSettings();
        $companies = $this->getCompanies();

        $this->info("Processing {$companies->count()} companies...");
        $bar = $this->output->createProgressBar($companies->count());
        $bar->start();

        $results = $companies->map(fn($company) => $this->processCompany($company, $module, $bar))->all();

        $bar->finish();
        $this->newLine();
        $this->displayResults($results);

        return self::SUCCESS;
    }

    private function setInputOptions()
    {
        if (empty($this->option('elementName'))) {
            throw new Exception("The --elementName option is required.");
        }
        $this->elementName = $this->option('elementName');

        if (empty($this->option('applyOn'))) {
            $this->applyOn = 'both';
        } else {
            $validOptions = ['setting', 'template', 'both'];
            if (!in_array($this->option('applyOn'), $validOptions)) {
                throw new Exception("Invalid value for --applyOn. Allowed values are: " . implode(', ', $validOptions));
            }
            $this->applyOn = $this->option('applyOn');
        }
    }

    private function getModuleWithSettings(): Model
    {
        $module = $this->moduleRepository->firstByAttributes(
            [['column' => 'Name', 'operand' => '=', 'value' => self::MODULE_NAME]],
            [
                'moduleSettings' => fn($query) => $query
                    ->whereIn('Name', self::MODULE_KEYS)
                    ->select(['Id', 'ModuleId', 'Name', 'DataType', 'Value'])
                    ->with([
                        'settings' => fn($q) => $q
                            ->select(['Id', 'ModuleSettingId', 'Value', 'CompanyId'])
                    ])
                    ->orderBy('Name')
            ],
            ['Id', 'Name']
        );

        if (!$module) {
            throw new Exception("Module '" . self::MODULE_NAME . "' not found");
        }

        // Transform to keyed collection and organize settings by company
        $module->moduleSettings = $module->moduleSettings->keyBy('Name');

        foreach ($module->moduleSettings as $moduleSetting) {
            $moduleSetting->settings = $moduleSetting->settings->keyBy('CompanyId');
        }

        return $module;
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

    private function processCompany(Model $company, Model $module, $bar): array
    {
        try {
            $counters = ['settings' => 0, 'templates' => 0];

            if (in_array($this->applyOn, ['setting', 'both'])) {
                $companySettings = $this->getCompanySpecificSettings($company->Id, $module);
                foreach ($companySettings as $setting) {
                    $updatedSetting = Arr::except($setting['Value'], $this->elementName);

                    $counters['settings'] = Setting::where('Id', $setting['Id'])
                        ->update(['Value' => json_encode($updatedSetting)]);
                }
            }

            if (in_array($this->applyOn, ['template', 'both'])) {
                CompanyService::setCompanyDatabaseConnection($company->Id);
                $counters['templates'] = $this->companyEmailTemplateRepository->deleteByAttributes([
                    ['column' => 'ElementName', 'operand' => '=', 'value' => $this->elementName]
                ]);
            }

            $status = $this->getFinalStatus($counters['settings'], $counters['templates']);
            return [$company->Name, $company->Id, $counters['settings'], $counters['templates'], $status];
        } catch (Exception $e) {
            Log::error("Failed to process company {$company->Id}: {$e->getMessage()}");
            return [$company->Name, $company->Id, 0, 0, "Error: {$e->getMessage()}"];
        } finally {
            $bar->advance();
        }
    }

    private function getCompanySpecificSettings(int $companyId, Model $module): array
    {
        $companySettings = [];
        foreach ($module->moduleSettings as $moduleSetting) {
            if ($moduleSetting->settings->has($companyId)) {
                $setting = $moduleSetting->settings[$companyId];
                $value = json_decode($setting->Value, true);

                $companySettings[$moduleSetting->Name] = [
                    'Id' => $setting->Id,
                    'Value' => $value
                ];
            }
        }

        return $companySettings;
    }

    private function getFinalStatus(int $settingCount, int $templateCount): string
    {
        return match (true) {
            $settingCount > 0 && $templateCount > 0 => 'Successful',
            $settingCount > 0 || $templateCount > 0 => 'Partially Successful',
            default => 'No Changes',
        };
    }

    private function displayResults(array $results): void
    {
        $this->table(['Name', 'Id', 'Settings', 'Templates', 'Status'], $results);
    }

}
