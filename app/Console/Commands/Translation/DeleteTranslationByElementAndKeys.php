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

class DeleteTranslationByElementAndKeys extends Command
{
    /**
     * The name and signature of the console command.
     * The console command description.
     *
     *   php artisan script:translation:add-element-or-key
     *   php artisan script:translation:add-element-or-key -C821 -S943
     *   php artisan script:translation:add-element-or-key --companyId=821 --skipCompanyId=943
     *   php artisan script:translation:delete-element-or-key --elementType=WebPage --element=element --keys=key1,key2, ...
     * @var string
     */
    protected $signature = 'script:translation:delete-element-or-key {--elementType=} {--E|element=} {--K|keys=} {--C|companyId=*} {--S|skipCompanyId=*}';
    protected $description = 'Delete translations by element and keys in CompanyTranslation for all companies or selected companies.';

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

    public function handle(): int
    {
        $elementType = $this->option('elementType');
        $elementName = $this->option('element');
        $keys = explode(",", $this->option('keys'));

        $companies = $this->getCompanies();

        $bar = $this->output->createProgressBar($companies->count() + 1);
        $bar->start();

        $this->processOffice($elementType, $elementName, $keys);
        $bar->advance();

        $companies->map(function ($company) use ($bar, $elementType, $elementName, $keys) {
            try {
                $this->processCompany($company, $elementType, $elementName, $keys);
            } catch (Exception $e) {
                Log::error("Error processing company {$company->Id} while deleting translation: " . $e->getMessage());
            }
            $bar->advance();
        });

        $bar->finish();
        $this->info(PHP_EOL);

        return Command::SUCCESS;
    }

    private function getCompanies(): Collection
    {
        $attributes = [
            ['column' => 'Disabled', 'operand' => '=', 'value' => '0']
        ];

        if ($inputCompanyIds = $this->option('companyId')) {
            $attributes[] = ['column' => 'Id', 'operand' => '=', 'value' => $inputCompanyIds];
        }

        if ($ignoredCompanyIds = $this->option('skipCompanyId')) {
            $attributes[] = ['column' => 'Id', 'operand' => '!=', 'value' => $ignoredCompanyIds];
        }

        return $this->companyRepository->getByAttributes($attributes);
    }

    private function processOffice($elementType, $elementName, $keys): void
    {
        $translations = $this->translationRepository->getByAttributes([
            ['column' => 'Type', 'operand' => '=', 'value' => $elementType],
            ['column' => 'ElementName', 'operand' => '=', 'value' => $elementName],
        ]);

        foreach ($translations as $translation) {
            $existingTranslation = $translation->Translations;
            foreach ($keys as $key) {
                unset($existingTranslation[$key]);
            }
            $translation->update(['Translations' => $existingTranslation]);
        }
    }

    /**
     * @throws Exception
     */
    private function processCompany($company, $elementType, $elementName, $keys): void
    {
        CompanyService::setCompanyDatabaseConnection($company->Id);

        $translations = $this->companyTranslationRepository->getByAttributes([
            ['column' => 'Type', 'operand' => '=', 'value' => $elementType],
            ['column' => 'ElementName', 'operand' => '=', 'value' => $elementName],
        ]);

        foreach ($translations as $translation) {
            $existingTranslation = $translation->Translations;
            foreach ($keys as $key) {
                unset($existingTranslation[$key]);
            }
            $translation->update(['Translations' => $existingTranslation]);
        }
    }
}
