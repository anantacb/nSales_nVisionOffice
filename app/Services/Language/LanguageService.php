<?php

namespace App\Services\Language;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\Language\LanguageRepositoryInterface;
use App\Repositories\Eloquent\Office\Translation\TranslationRepositoryInterface;
use Illuminate\Http\Request;

class LanguageService implements LanguageServiceInterface
{
    protected LanguageRepositoryInterface $languageRepository;
    protected TranslationRepositoryInterface $translationRepository;

    public function __construct(
        LanguageRepositoryInterface    $languageRepository,
        TranslationRepositoryInterface $translationRepository
    )
    {
        $this->languageRepository = $languageRepository;
        $this->translationRepository = $translationRepository;
    }

    public function getAllLanguages(Request $request): ServiceDto
    {
        $languages = $this->languageRepository->getByAttributes([], '', '', 'Name');
        return new ServiceDto("Languages retrieved!!!", 200, $languages);
    }

    public function create(Request $request): ServiceDto
    {
        $language = $this->languageRepository->create([
            'Name' => $request->input('Name'),
            'Locale' => $request->input('Locale'),
            'Code' => $request->input('Code'),
            'IsDefault' => $request->input('IsDefault')
        ]);
        return new ServiceDto("Language Created Successfully.", 200, $language);
    }

    public function update(Request $request): ServiceDto
    {
        $language = $this->languageRepository->findByIdAndUpdate(
            $request->input('Id'),
            [
                'Name' => $request->input('Name'),
                'Locale' => $request->input('Locale'),
                'Code' => $request->input('Code'),
                'IsDefault' => $request->input('IsDefault')
            ]
        );
        return new ServiceDto("Language Updated Successfully.", 200, $language);
    }

    public function getLanguages(Request $request): ServiceDto
    {
        $request = $request->all();
        $languages = $this->languageRepository->paginatedData($request);
        return new ServiceDto("Languages retrieved!!!", 200, $languages);
    }

    public function delete(Request $request): ServiceDto
    {
        $this->translationRepository->deleteByAttributes([
            ['column' => 'LanguageId', 'operand' => '=', 'value' => $request->input('LanguageId')]
        ]);
        $this->languageRepository->findByIdAndDelete($request->input('LanguageId'));
        return new ServiceDto("Language Deleted Successfully.", 200);
    }

    public function details(Request $request): ServiceDto
    {
        $relations = [];

        $language = $this->languageRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $request->input('LanguageId')]
        ], $relations);

        return new ServiceDto("Language Retrieved Successfully.", 200, $language);
    }
}
