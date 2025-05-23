<?php

namespace App\Services\EmailLayout;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Company\CompanyLanguage\CompanyLanguageRepositoryInterface;
use App\Repositories\Eloquent\Office\EmailLayout\EmailLayoutRepositoryInterface;
use App\Repositories\Eloquent\Office\EmailTemplate\EmailTemplateRepositoryInterface;
use App\Repositories\Eloquent\Office\TableField\TableFieldRepositoryInterface;
use App\Repositories\Eloquent\Office\Translation\TranslationRepositoryInterface;
use App\Services\ModuleSetting\ModuleSettingServiceInterface;
use Exception;
use Illuminate\Http\Request;

class EmailLayoutService extends EmailHelperService implements EmailLayoutServiceInterface
{
    protected EmailLayoutRepositoryInterface $layoutRepository;
    protected ModuleSettingServiceInterface $moduleSettingService;
    protected TranslationRepositoryInterface $translationRepository;
    protected EmailTemplateRepositoryInterface $templateRepository;
    protected CompanyLanguageRepositoryInterface $companyLanguageRepository;

    public function __construct(
        EmailLayoutRepositoryInterface     $layoutRepository,
        EmailTemplateRepositoryInterface   $templateRepository,
        ModuleSettingServiceInterface      $moduleSettingService,
        TranslationRepositoryInterface     $translationRepository,
        TableFieldRepositoryInterface      $tableFieldRepository,
        CompanyLanguageRepositoryInterface $companyLanguageRepository
    )
    {
        parent::__construct($tableFieldRepository);
        $this->layoutRepository = $layoutRepository;
        $this->templateRepository = $templateRepository;
        $this->moduleSettingService = $moduleSettingService;
        $this->translationRepository = $translationRepository;
        $this->companyLanguageRepository = $companyLanguageRepository;
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
            'Template' => $request->get('Template')
        ]);
        return new ServiceDto("Email Layout Created Successfully.", 200, $layout);
    }

    public function details(Request $request): ServiceDto
    {
        $relations = [];

        $layout = $this->layoutRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $request->get('EmailLayoutId')]
        ], $relations);

        return new ServiceDto("Layout Retrieved Successfully.", 200, $layout);
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

    public function getEmailLayoutOptionsByLanguage(Request $request): ServiceDto
    {
        $emailLayouts = $this->layoutRepository->getByAttributes(
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
//        $this->templateRepository->deleteByAttributes([
//            ['column' => 'LayoutId', 'operand' => '=', 'value' => $request->get('EmailLayoutId')]
//        ]);
        $this->layoutRepository->findByIdAndDelete($request->get('EmailLayoutId'));
        return new ServiceDto("Layout Deleted Successfully.", 200);
    }

    public function getPreviewTemplateObject(): ServiceDto
    {
        list('LayoutFields' => $layoutFields) = $this->moduleSettingService->getCoreModuleSettings(
            'Email',
            ['LayoutFields']
        );

        $layoutFields = json_decode(json_encode($layoutFields), true);
        $previewTemplateObject = $this->getEventProperties($layoutFields);

        return new ServiceDto("Preview template data retrieved successfully.", 200, $previewTemplateObject);
    }

    public function getEmailLayoutsForCompany(Request $request): ServiceDto
    {
        $request = $request->all();
        $companyLanguageCodes = $this->companyLanguageRepository->all()->pluck('Code')->toArray();

        $request['filter_by_relation'] = [
            ["relation" => 'language', "column" => "Code", "operator" => "=", "values" => $companyLanguageCodes]
        ];

        $request['relations'] = [
            ["name" => "language", "columns" => ['Id', 'Name', 'Code']]
        ];
        $layouts = $this->layoutRepository->paginatedData($request);

        return new ServiceDto("Layouts retrieved!!!", 200, $layouts);
    }

}
