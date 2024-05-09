<?php

namespace App\Services\CompanyLanguage;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Company\CompanyLanguage\CompanyLanguageRepositoryInterface;
use App\Repositories\Eloquent\Company\CompanyTranslation\CompanyTranslationRepositoryInterface;
use App\Repositories\Eloquent\Office\Language\LanguageRepositoryInterface;
use App\Repositories\Eloquent\Office\Translation\TranslationRepositoryInterface;
use Illuminate\Http\Request;

class CompanyLanguageService implements CompanyLanguageServiceInterface
{
    protected LanguageRepositoryInterface $languageRepository;
    protected TranslationRepositoryInterface $translationRepository;
    protected CompanyLanguageRepositoryInterface $companyLanguageRepository;
    protected CompanyTranslationRepositoryInterface $companyTranslationRepository;

    public function __construct(
        LanguageRepositoryInterface           $languageRepository,
        TranslationRepositoryInterface        $translationRepository,
        CompanyLanguageRepositoryInterface    $companyLanguageRepository,
        CompanyTranslationRepositoryInterface $companyTranslationRepository
    )
    {
        $this->languageRepository = $languageRepository;
        $this->translationRepository = $translationRepository;
        $this->companyLanguageRepository = $companyLanguageRepository;
        $this->companyTranslationRepository = $companyTranslationRepository;
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
        $this->companyTranslationRepository->deleteByAttributes([
            ['column' => 'CompanyLanguageId', 'operand' => '=', 'value' => $request->get('CompanyLanguageId')]
        ]);
        $this->companyLanguageRepository->findByIdAndDelete($request->get('CompanyLanguageId'));
        return new ServiceDto("Language Deleted Successfully.", 200);
    }

    public function addCompanyLanguage(Request $request): ServiceDto
    {
        $language = $this->languageRepository->findById($request->get('LanguageId'));
        $exists = $this->companyLanguageRepository->firstByAttributes([
            ['column' => 'Name', 'operand' => '=', 'value' => $language->Name]
        ]);
        if ($exists) {
            return new ServiceDto("Company Language Already Installed.", 200);
        }
        $companyLanguage = $this->companyLanguageRepository->firstOrCreate([
            'Name' => $language->Name,
            'Locale' => $language->Locale,
            'Code' => $language->Code,
            'IsDefault' => $language->IsDefault,
        ]);
        $translations = $this->translationRepository->getByAttribute('LanguageId', '=', $request->get('LanguageId'));
        foreach ($translations as $translation) {
            $this->companyTranslationRepository->create([
                'CompanyLanguageId' => $companyLanguage->Id,
                'Type' => $translation->Type,
                'ElementName' => $translation->ElementName,
                'Translations' => $translation->Translations,
            ]);
        }
        return new ServiceDto("Company Language Added Successfully.", 200);
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
