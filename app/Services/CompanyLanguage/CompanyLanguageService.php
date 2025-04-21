<?php

namespace App\Services\CompanyLanguage;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Company\CompanyEmailLayout\CompanyEmailLayoutRepositoryInterface;
use App\Repositories\Eloquent\Company\CompanyEmailTemplate\CompanyEmailTemplateRepositoryInterface;
use App\Repositories\Eloquent\Company\CompanyLanguage\CompanyLanguageRepositoryInterface;
use App\Repositories\Eloquent\Company\CompanyTranslation\CompanyTranslationRepositoryInterface;
use App\Repositories\Eloquent\Company\WebShopPage\WebShopPageRepositoryInterface;
use App\Repositories\Eloquent\Company\WebShopText\WebShopTextRepositoryInterface;
use App\Repositories\Eloquent\Office\EmailLayout\EmailLayoutRepositoryInterface;
use App\Repositories\Eloquent\Office\EmailTemplate\EmailTemplateRepositoryInterface;
use App\Repositories\Eloquent\Office\Language\LanguageRepositoryInterface;
use App\Repositories\Eloquent\Office\Translation\TranslationRepositoryInterface;
use App\Services\Company\CompanyService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CompanyLanguageService implements CompanyLanguageServiceInterface
{
    protected LanguageRepositoryInterface $languageRepository;
    protected TranslationRepositoryInterface $translationRepository;
    protected CompanyLanguageRepositoryInterface $companyLanguageRepository;
    protected CompanyTranslationRepositoryInterface $companyTranslationRepository;

    protected EmailLayoutRepositoryInterface $emailLayoutRepository;
    protected EmailTemplateRepositoryInterface $emailTemplateRepository;
    protected CompanyEmailLayoutRepositoryInterface $companyEmailLayoutRepository;
    protected CompanyEmailTemplateRepositoryInterface $companyEmailTemplateRepository;

    protected WebShopPageRepositoryInterface $webShopPageRepository;
    protected WebShopTextRepositoryInterface $webShopTextRepository;

    public function __construct(
        LanguageRepositoryInterface             $languageRepository,
        TranslationRepositoryInterface          $translationRepository,
        CompanyLanguageRepositoryInterface      $companyLanguageRepository,
        CompanyTranslationRepositoryInterface   $companyTranslationRepository,
        EmailLayoutRepositoryInterface          $emailLayoutRepository,
        EmailTemplateRepositoryInterface        $emailTemplateRepository,
        CompanyEmailLayoutRepositoryInterface   $companyEmailLayoutRepository,
        CompanyEmailTemplateRepositoryInterface $companyEmailTemplateRepository,
        WebShopPageRepositoryInterface          $webShopPageRepository,
        WebShopTextRepositoryInterface          $webShopTextRepository,
    )
    {
        $this->languageRepository = $languageRepository;
        $this->translationRepository = $translationRepository;
        $this->companyLanguageRepository = $companyLanguageRepository;
        $this->companyTranslationRepository = $companyTranslationRepository;
        $this->emailLayoutRepository = $emailLayoutRepository;
        $this->emailTemplateRepository = $emailTemplateRepository;
        $this->companyEmailLayoutRepository = $companyEmailLayoutRepository;
        $this->companyEmailTemplateRepository = $companyEmailTemplateRepository;
        $this->webShopPageRepository = $webShopPageRepository;
        $this->webShopTextRepository = $webShopTextRepository;
    }

    public function getAllCompanyLanguages(Request $request): ServiceDto
    {
        $companyLanguages = $this->companyLanguageRepository->getByAttributes([], '', '', 'Name');
        return new ServiceDto("Company Languages retrieved!!!", 200, $companyLanguages);
    }

    public function getCompanyLanguages(Request $request): ServiceDto
    {
        $request = $request->all();
        $companyLanguages = $this->companyLanguageRepository->paginatedData($request);
        return new ServiceDto("Company Languages retrieved!!!", 200, $companyLanguages);
    }

    public function delete(Request $request): ServiceDto
    {
        $count = $this->companyLanguageRepository->totalCount();
        $companyLanguage = $this->companyLanguageRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $request->get('CompanyLanguageId')]
        ]);
        if ($count == 1) {
            return new ServiceDto("You have to keep at least one language.", 422);
        }
        if ($companyLanguage->IsDefault) {
            return new ServiceDto("First you have to set another language as Default.", 422);
        }

        $this->companyTranslationRepository->deleteByAttributes([
            ['column' => 'CompanyLanguageId', 'operand' => '=', 'value' => $request->get('CompanyLanguageId')]
        ]);
        $this->companyEmailTemplateRepository->deleteByAttributes([
            ['column' => 'LanguageId', 'operand' => '=', 'value' => $request->get('CompanyLanguageId')]
        ]);
        $this->companyEmailLayoutRepository->deleteByAttributes([
            ['column' => 'LanguageId', 'operand' => '=', 'value' => $request->get('CompanyLanguageId')]
        ]);

        if (CompanyService::isModuleEnabled('WSPage')) {
            $this->webShopTextRepository->deleteByAttributes([
                ['column' => 'Language', 'operand' => '=', 'value' => $companyLanguage->Code],
                ['column' => 'ElementType', 'operand' => '=', 'value' => ['Page']]
            ]);
        }

        $companyLanguage->delete();

        if ($count - 1 == 1) {
            $this->companyLanguageRepository->updateAll([
                'IsDefault' => 1
            ]);
        }

        return new ServiceDto("Language Deleted Successfully.", 200);
    }

    public function addCompanyLanguage(Request $request): ServiceDto
    {
        $language = $this->languageRepository->findById($request->get('LanguageId'));
        $exists = $this->companyLanguageRepository->firstByAttributes([
            ['column' => 'Name', 'operand' => '=', 'value' => $language->Name]
        ]);
        if ($exists) {
            return new ServiceDto("Language($language->Name) Already Installed.", 200);
        }

        $companyLanguage = $this->createCompanyLanguage($language);
        $this->addCompanyTranslations($language, $companyLanguage);
        $this->addCompanyEmailLayoutAndTemplates($language, $companyLanguage);
        $this->addWebShopTexts($companyLanguage);

        return new ServiceDto("Company Language Added Successfully.", 200);
    }

    public function createCompanyLanguage($language, $isDefault = 0): Model
    {
        return $this->companyLanguageRepository->create([
            'Name' => $language->Name,
            'Locale' => $language->Locale,
            'Code' => $language->Code,
            'IsDefault' => $isDefault
        ]);
    }

    public function addCompanyTranslations($language, $companyLanguage): void
    {
        $translations = $this->translationRepository->getByAttribute('LanguageId', '=', $language->Id);
        foreach ($translations as $translation) {
            $this->companyTranslationRepository->create([
                'CompanyLanguageId' => $companyLanguage->Id,
                'Type' => $translation->Type,
                'ElementName' => $translation->ElementName,
                'Translations' => $translation->Translations,
            ]);
        }
    }

    public function addCompanyEmailLayoutAndTemplates($language, $companyLanguage): void
    {
        $emailTemplates = $this->emailTemplateRepository->getByAttribute('LanguageId', '=', $language->Id);
        $emailLayouts = $this->emailLayoutRepository->getByAttribute('LanguageId', '=', $language->Id);
        foreach ($emailLayouts as $emailLayout) {
            $companyEmailLayout = $this->companyEmailLayoutRepository->create([
                'Name' => $emailLayout->Name,
                'LanguageId' => $companyLanguage->Id,
                'Template' => $emailLayout->Template
            ]);
            $correspondingEmailTemplates = $emailTemplates->where('LayoutId', $emailLayout->Id)->where('LanguageId', $language->Id)->all();
            foreach ($correspondingEmailTemplates as $emailTemplate) {
                $this->companyEmailTemplateRepository->create([
                    'ElementName' => $emailTemplate->ElementName,
                    'LanguageId' => $companyLanguage->Id,
                    'Subject' => $emailTemplate->Subject,
                    'Template' => $emailTemplate->Template,
                    'LayoutId' => $companyEmailLayout->Id,
                ]);
            }
        }
    }

    public function addWebShopTexts($companyLanguage): void
    {
        /**
         * TODO
         * May have to add Item,Itemgroup etc
         */

        if (CompanyService::isModuleEnabled('WSPage')) {
            $webShopPages = $this->webShopPageRepository->all();
            $section_types = ['Header', 'SubHeader', 'Body', 'Footer'];
            if (CompanyService::isModuleEnabled('WSSEO')) {
                $section_types = array_merge(
                    $section_types,
                    ['SEOTitle', 'SEODescription', 'SEOKeyword', 'SEORobot']
                );
            }
            $webShopTextsForInsert = [];
            foreach ($webShopPages as $webShopPage) {
                foreach ($section_types as $sectionType) {
                    $webShopTextsForInsert[] = [
                        'InsertTime' => Carbon::now(),
                        'UpdateTime' => Carbon::now(),
                        'ElementNumber' => $webShopPage->Id,
                        'ElementType' => "Page",
                        'Type' => $sectionType,
                        'Language' => $companyLanguage->Code,
                        'Text' => ""
                    ];
                }
            }
            $this->webShopTextRepository->insert($webShopTextsForInsert);
        }
    }

    public function setAsDefaultLanguage(Request $request): ServiceDto
    {
        $this->companyLanguageRepository->updateAll([
            'IsDefault' => 0
        ]);
        $this->companyLanguageRepository->getByAttributesAndUpdate(
            [
                ['column' => 'Id', 'operand' => '=', 'value' => $request->get('CompanyLanguageId')],
            ],
            [
                'IsDefault' => 1
            ]
        );
        return new ServiceDto("Default Language Set Successfully.", 200);
    }
}
