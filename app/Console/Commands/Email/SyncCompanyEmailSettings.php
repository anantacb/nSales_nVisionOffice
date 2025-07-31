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
    private const MODULE_NAME = 'CompanyEmail';
    private const MODULE_KEYS = ['EmailEvents', 'LayoutFields'];
    private const LAYOUT_FIELDS_KEY = 'LayoutFields';
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
    protected $description = 'Synchronize missing values in EmailEvents, LayoutFields for companies with the CompanyEmail module';

    public function __construct(
        protected ModuleRepositoryInterface        $moduleRepository,
        protected CompanyModuleRepositoryInterface $companyModuleRepository,
        protected CompanyRepositoryInterface       $companyRepository
    )
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $module = $this->getModuleWithSettings();
            $defaultSettings = $this->formatDefaultSettings($module);
            $companies = $this->getCompanies($module);

            if ($companies->isEmpty()) {
                $this->info('No companies found to process.');
                return self::SUCCESS;
            }

            $this->processCompanies($companies, $module, $defaultSettings);

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error("Command failed: {$e->getMessage()}");
            Log::error('SyncCompanyEmailSettings failed', ['error' => $e->getMessage()]);
            return self::FAILURE;
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

    private function formatDefaultSettings(Model $module): array
    {
        return $module->moduleSettings->mapWithKeys(function ($setting) {
            $value = json_decode($setting->Value, true);

            if ($setting->Name === self::LAYOUT_FIELDS_KEY) {
                $value = collect($value)->keyBy('Field')->toArray();
            }

            return [$setting->Name => $value];
        })->toArray();
    }

    private function getCompanies(Model $module): Collection
    {
        $moduleSettingIds = $module->moduleSettings->pluck('Id')->toArray();

        $settingOverrideCompanyIds = Setting::whereIn('ModuleSettingId', $moduleSettingIds)
            ->distinct()
            ->pluck('CompanyId')
            ->toArray();

        $installedCompanyIds = $this->companyModuleRepository
            ->getByAttributes([
                ['column' => 'ModuleId', 'operand' => '=', 'value' => $module->Id]
            ], [], ['CompanyId'])
            ->pluck('CompanyId')
            ->toArray();

        $targetCompanyIds = array_intersect($installedCompanyIds, $settingOverrideCompanyIds);

        $attributes = [
            ['column' => 'Disabled', 'operand' => '=', 'value' => '0'],
            ['column' => 'Id', 'operand' => '=', 'value' => $targetCompanyIds]
        ];

        if ($inputCompanyIds = $this->option('companyId')) {
            $attributes[] = ['column' => 'Id', 'operand' => '=', 'value' => $inputCompanyIds];
        }

        if ($skipCompanyIds = $this->option('skipCompanyId')) {
            $attributes[] = ['column' => 'Id', 'operand' => '!=', 'value' => $skipCompanyIds];
        }

        return $this->companyRepository->getByAttributes($attributes);
    }

    private function processCompanies(Collection $companies, Model $module, array $defaultSettings): void
    {
        $this->info("Processing {$companies->count()} companies...");
        $bar = $this->output->createProgressBar($companies->count());
        $bar->start();

        $results = [];

        foreach ($companies as $company) {
            try {
                $result = $this->processCompany($company, $module, $defaultSettings);
                $results[] = $result;
            } catch (Exception $e) {
                Log::error("Failed to process company {$company->Id}", ['error' => $e->getMessage()]);
                $results[] = [$company->Name, $company->Id, 0, 0, "Error: {$e->getMessage()}"];
            } finally {
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->displayResults($results);
    }

    private function processCompany(Model $company, Model $module, array $defaultSettings): array
    {
        $counters = ['EmailEvents' => 0, 'LayoutFields' => 0];
        $companySettings = $this->getCompanySpecificSettings($company->Id, $module);

        foreach ($companySettings as $settingName => $setting) {
            $mergedSettings = $this->mergeWithDefaults(
                $defaultSettings[$settingName],
                $setting['Value']
            );

            if ($mergedSettings !== $setting['Value']) {
                $updateValue = $settingName === self::LAYOUT_FIELDS_KEY
                    ? array_values($mergedSettings)
                    : $mergedSettings;

                $counters[$settingName] = Setting::where('Id', $setting['Id'])
                    ->update(['Value' => json_encode($updateValue)]);
            }
        }

        $status = $this->determineStatus($counters['EmailEvents'], $counters['LayoutFields']);
        return [$company->Name, $company->Id, $counters['EmailEvents'], $counters['LayoutFields'], $status];
    }

    private function getCompanySpecificSettings(int $companyId, Model $module): array
    {
        $companySettings = [];
        foreach ($module->moduleSettings as $moduleSetting) {
            if ($moduleSetting->settings->has($companyId)) {
                $setting = $moduleSetting->settings[$companyId];
                $value = json_decode($setting->Value, true);

                if ($moduleSetting->Name === self::LAYOUT_FIELDS_KEY) {
                    $value = collect($value)->keyBy('Field')->toArray();
                }

                $companySettings[$moduleSetting->Name] = [
                    'Id' => $setting->Id,
                    'Value' => $value
                ];
            }
        }

        return $companySettings;
    }

    private function mergeWithDefaults(array $defaults, array $custom): array
    {
        return array_merge($defaults, $custom);
    }

    private function determineStatus(int $emailEvents, int $layoutFields): string
    {
        return match (true) {
            $emailEvents > 0 && $layoutFields > 0 => 'Successful',
            $emailEvents > 0 || $layoutFields > 0 => 'Partially Successful',
            default => 'No Changes',
        };
    }

    private function displayResults(array $results): void
    {
        if (empty($results)) {
            $this->info('No results to display.');
            return;
        }

        $this->table(['Name', 'ID', 'EmailEvents', 'LayoutFields', 'Status'], $results);

        // Display summary
        $summary = collect($results)->groupBy(4)->map->count();
        $this->newLine();
        $this->info('Summary:');
        foreach ($summary as $status => $count) {
            $this->line("  {$status}: {$count}");
        }
    }
}
