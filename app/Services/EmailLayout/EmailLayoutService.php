<?php

namespace App\Services\EmailLayout;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\EmailLayout\EmailLayoutRepositoryInterface;
use App\Repositories\Eloquent\Office\Translation\TranslationRepositoryInterface;
use Illuminate\Http\Request;

class EmailLayoutService implements EmailLayoutServiceInterface
{
    protected EmailLayoutRepositoryInterface $layoutRepository;
    protected TranslationRepositoryInterface $translationRepository;

    public function __construct(
        EmailLayoutRepositoryInterface $layoutRepository,
        TranslationRepositoryInterface $translationRepository
    )
    {
        $this->layoutRepository = $layoutRepository;
        $this->translationRepository = $translationRepository;
    }

    public function getEmailLayouts(Request $request): ServiceDto
    {
        $request = $request->all();
        $request['relations'] = [
            [
                "name" => "language", "columns" => ['Id', 'Name']
            ]
        ];
        $layouts = $this->layoutRepository->paginatedData($request);
        return new ServiceDto("Layouts retrieved!!!", 200, $layouts);
    }

    public function create(Request $request): ServiceDto
    {
        $layout = $this->layoutRepository->create([
            'Name' => $request->get('Name'),
            'LanguageId' => $request->get('LanguageId'),
//            'Template' => $request->get('Template')
            'Template' => 'test'
        ]);
        return new ServiceDto("Email Layout Created Successfully.", 200, $layout);
    }

    public function update(Request $request): ServiceDto
    {
        $layout = $this->layoutRepository->findByIdAndUpdate(
            $request->get('Id'),
            [
                'Name' => $request->get('Name'),
                'LanguageId' => $request->get('LanguageId'),
                'Template' => $request->get('Template')
            ]
        );
        return new ServiceDto("Layout Updated Successfully.", 200, $layout);
    }


    public function delete(Request $request): ServiceDto
    {
//        $this->translationRepository->deleteByAttributes([
//            ['column' => 'LanguageId', 'operand' => '=', 'value' => $request->get('LanguageId')]
//        ]);
        $this->layoutRepository->findByIdAndDelete($request->get('LanguageId'));
        return new ServiceDto("Layout Deleted Successfully.", 200);
    }

    public function details(Request $request): ServiceDto
    {
        $relations = [];

        $layout = $this->layoutRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $request->get('EmailLayoutId')]
        ], $relations);

        return new ServiceDto("Layout Retrieved Successfully.", 200, $layout);
    }
}
