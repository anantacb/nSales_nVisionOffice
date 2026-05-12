<?php

namespace App\Services\Translation;

use App\Contracts\ServiceDto;
use App\Jobs\SyncTranslations;
use App\Repositories\Eloquent\Office\Translation\TranslationRepositoryInterface;
use Illuminate\Http\Request;

class TranslationService implements TranslationServiceInterface
{
    protected TranslationRepositoryInterface $translationRepository;

    public function __construct(TranslationRepositoryInterface $translationRepository)
    {
        $this->translationRepository = $translationRepository;
    }

    public function getTranslations(Request $request): ServiceDto
    {
        $request = $request->all();
        $request['relations'] = [
            [
                "name" => "language", "columns" => ['Id', 'Name']
            ]
        ];
        $languages = $this->translationRepository->paginatedData($request);
        return new ServiceDto("Languages retrieved!!!", 200, $languages);
    }

    public function create(Request $request): ServiceDto
    {
        $translation = $this->translationRepository->create([
            'LanguageId' => $request->input('LanguageId'),
            'Type' => $request->input('Type'),
            'ElementName' => $request->input('ElementName'),
            'Translations' => $request->input('Translations')
        ]);
        return new ServiceDto("Translation Created Successfully.", 200, $translation);
    }

    public function update(Request $request): ServiceDto
    {
        $translation = $this->translationRepository->findByIdAndUpdate(
            $request->input('Id'),
            [
                'LanguageId' => $request->input('LanguageId'),
                'Type' => $request->input('Type'),
                'ElementName' => $request->input('ElementName'),
                'Translations' => $request->input('Translations')
            ]
        );
        return new ServiceDto("Translation Updated Successfully.", 200, $translation);
    }

    public function delete(Request $request): ServiceDto
    {
        $this->translationRepository->findByIdAndDelete($request->input('TranslationId'));
        return new ServiceDto("Translation Deleted Successfully.", 200);
    }

    public function details(Request $request): ServiceDto
    {
        $relations = ['language'];

        $translation = $this->translationRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $request->input('TranslationId')]
        ], $relations);

        return new ServiceDto("Translation Retrieved Successfully.", 200, $translation);
    }

    public function sync(): ServiceDto
    {
        SyncTranslations::dispatch()->onQueue('translations');
        return new ServiceDto("Translations will be available for all elements and companies in a few moments. Please check after sometime.", 200, []);
    }
}
