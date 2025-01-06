<?php

namespace App\Services\CompanyEmailLayout;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Company\CompanyEmailLayout\CompanyEmailLayoutRepositoryInterface;
use App\Repositories\Eloquent\Office\Translation\TranslationRepositoryInterface;
use App\Services\Company\CompanyService;
use App\Services\EmailLayout\EmailHelperService;
use Exception;
use Illuminate\Http\Request;

class CompanyEmailLayoutService extends EmailHelperService implements CompanyEmailLayoutServiceInterface
{
    protected CompanyEmailLayoutRepositoryInterface $repository;
    protected TranslationRepositoryInterface $translationRepository;

    public function __construct(
        CompanyEmailLayoutRepositoryInterface $repository,
        TranslationRepositoryInterface        $translationRepository
    )
    {
        $this->repository = $repository;
        $this->translationRepository = $translationRepository;
    }

    public function getEmailLayouts(Request $request): ServiceDto
    {
        $request = $request->all();
        $request['relations'] = [
            [
                "name" => "companyLanguage", "columns" => ['Id', 'Name']
            ]
        ];
        $layouts = $this->repository->paginatedData($request);
        return new ServiceDto("Layouts retrieved!!!", 200, $layouts);
    }

    public function create(Request $request): ServiceDto
    {
        $layout = $this->repository->create([
            'Name' => $request->get('Name'),
            'LanguageId' => $request->get('LanguageId'),
            'Template' => $request->get('Template')
        ]);
        return new ServiceDto("Email Layout Created Successfully.", 200, $layout);
    }

    public function details(Request $request): ServiceDto
    {
        $relations = [];

        $layout = $this->repository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $request->get('EmailLayoutId')]
        ], $relations);

        return new ServiceDto("Layout Retrieved Successfully.", 200, $layout);
    }

    public function update(Request $request): ServiceDto
    {
        $layout = $this->repository->findByIdAndUpdate(
            $request->get('Id'),
            [
                'Name' => $request->get('Name'),
                'LanguageId' => $request->get('LanguageId'),
                'Template' => $request->get('Template')
            ]
        );
        return new ServiceDto("Layout Updated Successfully.", 200, $layout);
    }

    public function getEmailLayoutOptionsByLanguage(Request $request): ServiceDto
    {
        $emailLayouts = $this->repository->getByAttributes(
            [
                ['column' => 'LanguageId', 'operand' => '=', 'value' => $request->get('LanguageId')]
            ]
            , '', '', 'Name'
        );


        return new ServiceDto("Layouts retrieved!!!", 200, $emailLayouts);
    }

    /**
     * @throws Exception
     */
    public function getDataForPreview(Request $request): ServiceDto
    {
        $preview = $this->renderTemplateAndSubject(
            $request['Template'],
            "Content Goes here",
            "",
            $request['TemplateObject']
        );

        return new ServiceDto("Preview data retrieved Successfully.", 200, $preview);
    }

    public function delete(Request $request): ServiceDto
    {
//        $this->translationRepository->deleteByAttributes([
//            ['column' => 'LanguageId', 'operand' => '=', 'value' => $request->get('EmailLayoutId')]
//        ]);
        $this->repository->findByIdAndDelete($request->get('EmailLayoutId'));
        return new ServiceDto("Layout Deleted Successfully.", 200);
    }

    public function getPreviewTemplateObject(): ServiceDto
    {
        $layoutFields = json_decode(CompanyService::getSettingValue('CompanyEmail', 'LayoutFields'), true);
        $previewTemplateObject = $this->getEventProperties($layoutFields ?? []);

        return new ServiceDto("Preview template data retrieved successfully.", 200, $previewTemplateObject);
    }

}
