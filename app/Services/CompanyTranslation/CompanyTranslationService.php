<?php

namespace App\Services\CompanyTranslation;

use App\Contracts\ServiceDto;
use App\Jobs\SyncCompanyTranslations;
use App\Repositories\Eloquent\Company\CompanyTranslation\CompanyTranslationRepositoryInterface;
use Illuminate\Http\Request;

class CompanyTranslationService implements CompanyTranslationServiceInterface
{
    protected CompanyTranslationRepositoryInterface $companyTranslationRepository;

    public function __construct(CompanyTranslationRepositoryInterface $companyTranslationRepository)
    {
        $this->companyTranslationRepository = $companyTranslationRepository;
    }

    public function getCompanyTranslations(Request $request): ServiceDto
    {
        $request = $request->all();
        $request['relations'] = [
            [
                "name" => "companyLanguage", "columns" => ['Id', 'Name']
            ]
        ];
        $languages = $this->companyTranslationRepository->paginatedData($request);
        return new ServiceDto("Company Languages retrieved!!!", 200, $languages);
    }

    public function syncCompanyTranslations(Request $request): ServiceDto
    {
        SyncCompanyTranslations::dispatch($request->get('CompanyId'))->onQueue('translations');
        return new ServiceDto("Translations will be available for all elements in a few moments. Please check after sometime.", 200, []);
    }

    public function create(Request $request): ServiceDto
    {
        $companyTranslation = $this->companyTranslationRepository->create([
            'CompanyLanguageId' => $request->get('CompanyLanguageId'),
            'Type' => $request->get('Type'),
            'ElementName' => $request->get('ElementName'),
            'Translations' => $request->get('Translations')
        ]);
        return new ServiceDto("Translation Created Successfully.", 200, $companyTranslation);
    }

    public function update(Request $request): ServiceDto
    {
        $translation = $this->companyTranslationRepository->findByIdAndUpdate(
            $request->get('Id'),
            [
                'CompanyLanguageId' => $request->get('CompanyLanguageId'),
                'Type' => $request->get('Type'),
                'ElementName' => $request->get('ElementName'),
                'Translations' => $request->get('Translations')
            ]
        );
        return new ServiceDto("Translation Updated Successfully.", 200, $translation);
    }

    public function delete(Request $request): ServiceDto
    {
        $this->companyTranslationRepository->findByIdAndDelete($request->get('CompanyTranslationId'));
        return new ServiceDto("Translation Deleted Successfully.", 200);
    }

    public function details(Request $request): ServiceDto
    {
        $relations = ['companyLanguage'];

        $translation = $this->companyTranslationRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $request->get('CompanyTranslationId')]
        ], $relations);

        return new ServiceDto("Company Translation Retrieved Successfully.", 200, $translation);
    }
}
