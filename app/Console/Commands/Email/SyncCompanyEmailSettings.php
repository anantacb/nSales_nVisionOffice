<?php

namespace App\Console\Commands\Email;

use App\Models\Office\Setting;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyModule\CompanyModuleRepositoryInterface;
use App\Repositories\Eloquent\Office\Module\ModuleRepositoryInterface;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class SyncCompanyEmailSettings extends Command
{
    /**
     * The name and signature of the console command.
     * The console command description.
     *
     *   php artisan script:sync-company-email-settings
     *   php artisan script:sync-company-email-settings -C821 -S943
     *   php artisan script:sync-company-email-settings --companyId=821 --skipCompanyId=943
     * @var string
     */
    protected $signature = 'script:sync-company-email-settings
                            {--C|companyId=* : Specific company IDs to process}
                            {--S|skipCompanyId=* : Company IDs to skip}';

    protected $description = 'Synchronize missing email layouts and templates for companies.';

    protected ModuleRepositoryInterface $moduleRepository;
    protected CompanyModuleRepositoryInterface $companyModuleRepository;
    protected CompanyRepositoryInterface $companyRepository;
    protected string $moduleName = 'CompanyEmail';
    protected array $moduleKeys = ['EmailEvents', 'LayoutFields'];
    protected array $defaultModuleSettings;
    protected Model $module;

    public function __construct(
        ModuleRepositoryInterface        $moduleRepository,
        CompanyModuleRepositoryInterface $companyModuleRepository,
        CompanyRepositoryInterface       $companyRepository
    )
    {
        parent::__construct();
        $this->moduleRepository = $moduleRepository;
        $this->companyModuleRepository = $companyModuleRepository;
        $this->companyRepository = $companyRepository;
    }

    /**
     * Fetch default CompanyEmail Module Settings value
     * For Each Company
     */
    /**
     * get CompanyEmail Module Settings value
     *   Check if CompanyEmail Module Settings value is different from default
     *     If different, update CompanyEmail Module Settings value
     *
     */
    public function handle(): int
    {
        $companies = $this->getCompanies();
        $this->defaultModuleSettings = $this->formatDefaultSettingsData($this->module);

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
        $this->module = $this->getModuleWithModuleSettings();
        $this->module->moduleSettings = $this->module->moduleSettings->keyBy('Name');
        $moduleSettingsIds = $this->module->moduleSettings->pluck('Id')->toArray();
        $settingOverrideCompanyIds = Setting::whereIn('ModuleSettingId', $moduleSettingsIds)
            ->select(['Id', 'CompanyId'])->pluck('CompanyId')->toArray();

        $installedCompanyIds = $this->companyModuleRepository->getByAttributes([
            ['column' => 'ModuleId', 'operand' => '=', 'value' => $this->module->Id]
        ], [], ['CompanyId'])->pluck('CompanyId')->toArray();

        $installedAndOverrideCompanyIds = array_intersect($installedCompanyIds, $settingOverrideCompanyIds);

        $attributes = [
            ['column' => 'Disabled', 'operand' => '=', 'value' => '0'],
            ['column' => 'Id', 'operand' => '=', 'value' => $installedAndOverrideCompanyIds]
        ];

        if ($inputCompanyIds = $this->option('companyId')) {
            $attributes[] = ['column' => 'Id', 'operand' => '=', 'value' => $inputCompanyIds];
        }

        if ($ignoredCompanyIds = $this->option('skipCompanyId')) {
            $attributes[] = ['column' => 'Id', 'operand' => '!=', 'value' => $ignoredCompanyIds];
        }

        return $this->companyRepository->getByAttributes($attributes);
    }

    private function getModuleWithModuleSettings(): Model
    {
        return $this->moduleRepository->firstByAttributes(
            [['column' => 'Name', 'operand' => '=', 'value' => $this->moduleName]],
            [
                'moduleSettings' => function ($que) {
                    $que->whereIn('Name', $this->moduleKeys)
                        ->select(['Id', 'ModuleId', 'Name', 'DataType', 'Value'])
                        ->with([
                            'settings' => function ($q) {
                                $q->select(["Id", "ModuleSettingId", "Value", "CompanyId"]);
                            }
                        ])
                        ->orderBy('Name');
                }
            ],
            ['Id', 'Name'], 'Name'
        );
    }

    private function formatDefaultSettingsData($module): array
    {
        $moduleSettings = [];
        if ($module->moduleSettings) {
            foreach ($module->moduleSettings as $key => $moduleSetting) {
                $this->module->moduleSettings[$key]->settings = $moduleSetting->settings->keyBy('CompanyId');
                $moduleSettingValue = json_decode($moduleSetting->Value, true);

                if ($moduleSetting->Name === 'LayoutFields') {
                    $moduleSettingValue = collect($moduleSettingValue)->keyBy('Field')->toArray();
                }
                $moduleSettings[$moduleSetting->Name] = $moduleSettingValue;
            }
        }

        return $moduleSettings;
    }

    private function processCompany(Model $company, $bar): array
    {
        try {
            $counters = ['EmailEvents' => 0, 'LayoutFields' => 0];
            $companySettings = $this->getCompanySpecificSettings($company->Id);

            foreach ($companySettings as $key => $setting) {
                $mergedSettings = $this->mergeSettingsWithDefaults(
                    $this->defaultModuleSettings[$key], $setting->Value
                );

                // Compare merged result with current value # No change needed
                if ($mergedSettings === $setting->Value) {
                    continue;
                }
                $tobeUpdateSetting = $key === 'LayoutFields' ? array_values($mergedSettings) : $mergedSettings;

                $counters[$key] = Setting::where('Id', $setting->Id)
                    ->update(['Value' => json_encode($tobeUpdateSetting)]);
            }

            $status = $this->getFinalStatus($counters['EmailEvents'], $counters['LayoutFields']);
            return [$company->Name, $company->Id, $counters['EmailEvents'], $counters['LayoutFields'], $status];
        } catch (Exception $e) {
            Log::error("Failed to process company {$company->Id}: {$e->getMessage()}");
            return [$company->Name, $company->Id, 0, 0, "Error: {$e->getMessage()}"];
        } finally {
            $bar->advance();
        }
    }

    private function getCompanySpecificSettings($companyId): array
    {
        $companySettings = [];
        foreach ($this->module->moduleSettings as $moduleSetting) {
            // If the setting has a company-specific value, use it
            if ($moduleSetting->settings->has($companyId)) {
                $tempSettings = $moduleSetting->settings[$companyId];
                $tempSettings->Value = json_decode($tempSettings->Value, true);

                if ($moduleSetting->Name === 'LayoutFields') {
                    $tempSettings->Value = collect($tempSettings->Value)->keyBy('Field')->toArray();
                }

                $companySettings[$moduleSetting->Name] = $tempSettings;
            }
        }
        return $companySettings;
    }

    private function mergeSettingsWithDefaults(array $default, array $custom): array
    {
        $output = $custom;
        foreach ($default as $key => $defaultValue) {
            if (!array_key_exists($key, $custom)) {
                $output[$key] = $defaultValue;
            }
        }

        return $output;
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
        $this->table(['Name', 'Id', 'EmailEvents', 'LayoutFields', 'Status'], $results);
    }
}
