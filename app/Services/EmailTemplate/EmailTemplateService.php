<?php

namespace App\Services\EmailTemplate;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Company\CompanyLanguage\CompanyLanguageRepositoryInterface;
use App\Repositories\Eloquent\Office\EmailLayout\EmailLayoutRepositoryInterface;
use App\Repositories\Eloquent\Office\EmailTemplate\EmailTemplateRepositoryInterface;
use App\Services\EmailLayout\EmailHelperService;
use App\Services\ModuleSetting\ModuleSettingServiceInterface;
use Exception;
use Illuminate\Http\Request;

class EmailTemplateService extends EmailHelperService implements EmailTemplateServiceInterface
{
    protected EmailTemplateRepositoryInterface $templateRepository;
    protected EmailLayoutRepositoryInterface $emailLayoutRepository;
    protected ModuleSettingServiceInterface $moduleSettingService;
    protected CompanyLanguageRepositoryInterface $companyLanguageRepository;

    public function __construct(
        EmailTemplateRepositoryInterface   $templateRepository,
        EmailLayoutRepositoryInterface     $emailLayoutRepository,
        ModuleSettingServiceInterface      $moduleSettingService,
        CompanyLanguageRepositoryInterface $companyLanguageRepository,
    )
    {
        $this->templateRepository = $templateRepository;
        $this->emailLayoutRepository = $emailLayoutRepository;
        $this->moduleSettingService = $moduleSettingService;
        $this->companyLanguageRepository = $companyLanguageRepository;
    }

    public function getEmailTemplates(Request $request): ServiceDto
    {
        $request = $request->all();
        $request['relations'] = [
            ["name" => "language", "columns" => ['Id', 'Name']],
            ["name" => "emailLayout", "columns" => ['Id', 'Name']]
        ];
        $templates = $this->fetchEmailTemplates($request);

        return new ServiceDto("Templates retrieved!!!", 200, $templates);
    }

    public function fetchEmailTemplates($request)
    {
        $templates = $this->templateRepository->paginatedData($request);

        list('EmailEvents' => $emailEvents) = $this->moduleSettingService->getCoreModuleSettings(
            'Email', ['EmailEvents']
        );
        $emailEvents = json_decode(json_encode($emailEvents), true);

        return $templates->map(function ($template) use ($emailEvents) {
            $template->ModifiedElementName = $emailEvents[$template->ElementName]['Title'] ?? $template->ElementName;
            return $template;
        });
    }

    public function create(Request $request): ServiceDto
    {
        $layout = $this->templateRepository->create([
            'ElementName' => $request->get('ElementName'),
            'LayoutId' => $request->get('LayoutId'),
            'LanguageId' => $request->get('LanguageId'),
            'Subject' => $request->get('Subject'),
            'Template' => $request->get('Template')
        ]);
        return new ServiceDto("Email Template Created Successfully.", 200, $layout);
    }

    public function details(Request $request): ServiceDto
    {
        $relations = [];

        $layout = $this->templateRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $request->get('EmailTemplateId')]
        ], $relations);

        return new ServiceDto("Template Retrieved Successfully.", 200, $layout);
    }

    public function update(Request $request): ServiceDto
    {
        $layout = $this->templateRepository->findByIdAndUpdate(
            $request->get('Id'),
            [
                'ElementName' => $request->get('ElementName'),
                'LayoutId' => $request->get('LayoutId'),
                'LanguageId' => $request->get('LanguageId'),
                'Subject' => $request->get('Subject'),
                'Template' => $request->get('Template')
            ]
        );
        return new ServiceDto("Template Updated Successfully.", 200, $layout);
    }

    public function getEmailEvents(Request $request): ServiceDto
    {
        $emailEvents = $this->fetchEmailEvents();

        return new ServiceDto("Email events retrieved!!!", 200, $emailEvents);
    }

    public function fetchEmailEvents()
    {
        list('LayoutFields' => $layoutFields, 'EmailEvents' => $emailEvents) = $this->moduleSettingService->getCoreModuleSettings(
            'Email',
            ['LayoutFields', 'EmailEvents']
        );
        $layoutFields = json_decode(json_encode($layoutFields), true);
        $emailEvents = json_decode(json_encode($emailEvents), true);

        foreach ($emailEvents as $key => $emailEvent) {
            $emailEvents[$key]['Fields'] = array_merge($layoutFields, $emailEvent['Fields']);
            $emailEvents[$key]['templateObject'] = $this->getEventProperties(
                array_merge($layoutFields, $emailEvent['Fields'])
            );
        }

        return $emailEvents;
    }

    /**
     * @throws Exception
     */
    public function getDataForPreview(Request $request): ServiceDto
    {
        $emailLayout = $this->emailLayoutRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $request['LayoutId']]
        ]);

        $preview = $this->renderTemplateAndSubject(
            $emailLayout->Template,
            $request['Template'],
            $request['Subject'],
            $request['TemplateObject']
        );

        return new ServiceDto("Preview data retrieved Successfully.", 200, $preview);
    }

    public function delete(Request $request): ServiceDto
    {
        $this->templateRepository->findByIdAndDelete($request->get('EmailTemplateId'));
        return new ServiceDto("Template Deleted Successfully.", 200);
    }

    public function getEmailTemplatesForCompany(Request $request): ServiceDto
    {
        $request = $request->all();
        $companyLanguageCodes = $this->companyLanguageRepository->all()->pluck('Code')->toArray();

        $request['filter_by_relation'] = [
            ["relation" => 'language', "column" => "Code", "operator" => "=", "values" => $companyLanguageCodes]
        ];

        $request['relations'] = [
            ["name" => "language", "columns" => ['Id', 'Name', 'Code']],
            ["name" => "emailLayout", "columns" => ['Id', 'Name']]
        ];
        $templates = $this->fetchEmailTemplates($request);

        return new ServiceDto("Templates retrieved!!!", 200, $templates);
    }


}
