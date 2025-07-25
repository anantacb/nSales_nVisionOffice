<?php

namespace App\Services\CompanyEmailTemplate;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Company\CompanyEmailLayout\CompanyEmailLayoutRepositoryInterface;
use App\Repositories\Eloquent\Company\CompanyEmailTemplate\CompanyEmailTemplateRepositoryInterface;
use App\Repositories\Eloquent\Office\TableField\TableFieldRepositoryInterface;
use App\Services\Company\CompanyService;
use App\Services\EmailLayout\EmailHelperService;
use App\Services\ModuleSetting\ModuleSettingServiceInterface;
use Exception;
use Illuminate\Http\Request;

class CompanyEmailTemplateService extends EmailHelperService implements CompanyEmailTemplateServiceInterface
{
    protected CompanyEmailTemplateRepositoryInterface $templateRepository;
    protected CompanyEmailLayoutRepositoryInterface $emailLayoutRepository;
    protected ModuleSettingServiceInterface $moduleSettingService;

    public function __construct(
        CompanyEmailTemplateRepositoryInterface $templateRepository,
        CompanyEmailLayoutRepositoryInterface   $emailLayoutRepository,
        ModuleSettingServiceInterface           $moduleSettingService,
        TableFieldRepositoryInterface           $tableFieldRepository
    )
    {
        parent::__construct($tableFieldRepository);
        $this->templateRepository = $templateRepository;
        $this->emailLayoutRepository = $emailLayoutRepository;
        $this->moduleSettingService = $moduleSettingService;
    }

    public function getEmailTemplates(Request $request): ServiceDto
    {
        $request = $request->all();
        $request['relations'] = [
            ["name" => "companyLanguage", "columns" => ['Id', 'Name']],
            ["name" => "companyEmailLayout", "columns" => ['Id', 'Name']]
        ];
        $templates = $this->templateRepository->paginatedData($request);

        return new ServiceDto("Templates retrieved!!!", 200, $templates);
    }

    public function details(Request $request): ServiceDto
    {
        $relations = [];

        $template = $this->templateRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $request->get('EmailTemplateId')]
        ], $relations);

        return new ServiceDto("Template Retrieved Successfully.", 200, $template);
    }

    public function update(Request $request): ServiceDto
    {
        $template = $this->templateRepository->findByIdAndUpdate(
            $request->get('Id'),
            [
                'ElementName' => $request->get('ElementName'),
                'LayoutId' => $request->get('LayoutId'),
                'LanguageId' => $request->get('LanguageId'),
                'Subject' => $request->get('Subject'),
                'Template' => $request->get('Template'),
                'DatabaseTable' => $request->get('DatabaseTable'),
                'TableColumn' => $request->get('TableColumn'),
                'ColumnValue' => $request->get('ColumnValue'),
            ]
        );
        return new ServiceDto("Template Updated Successfully.", 200, $template);
    }

    public function getEmailEvents(Request $request): ServiceDto
    {
        $emailEvents = $this->fetchEmailEvents($request);

        return new ServiceDto("Email events retrieved!!!", 200, $emailEvents);
    }

    public function fetchEmailEvents($request): array
    {
        $emailEvents = json_decode(CompanyService::getSettingValue('CompanyEmail', 'EmailEvents'), true);
        $layoutFields = json_decode(CompanyService::getSettingValue('CompanyEmail', 'LayoutFields'), true);
        $layoutFields = array_merge(
            $this->getEventProperties($layoutFields ?? []),
            array_filter($this->getCompanyDataForTemplate(), function ($value) {
                return $value !== '';
            })
        );

        $data = [];
        foreach ($emailEvents as $key => $emailEvent) {
            $fields = array_merge($layoutFields, $this->getEventProperties($emailEvent['Fields'] ?? []));

            // Fetch fields from main table
            if (isset($emailEvent['Table'])) {
                $tableFields = $this->fetchTableFields($emailEvent['Table'], $request->get("CompanyId"));
                $fields = array_merge($tableFields, $fields);
            }

            // Handle children recursively
            if (!empty($emailEvent['Children']) && is_array($emailEvent['Children'])) {
                foreach ($emailEvent['Children'] as $child) {
                    $fields = $this->assignRelation($fields, $child, $request->get("CompanyId"));
                }
            }

            $data[$key] = [
                'Title' => $emailEvent['Title'],
                'templateObject' => $fields,
            ];
        }

        return $data;
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

    public function copyTemplateToCompany(Request $request): ServiceDto
    {
        $companyEmailTemplate = $this->templateRepository->create([
            'ElementName' => $request->get('ElementName'),
            'LanguageId' => $request->get('LanguageId'),
            'LayoutId' => $request->get('LayoutId'),
            'Subject' => $request->get('Subject'),
            'Template' => $request->get('Template')
        ]);

        return new ServiceDto("Email Template Copied Successfully.", 200, $companyEmailTemplate);
    }

    public function create(Request $request): ServiceDto
    {
        $template = $this->templateRepository->create([
            'ElementName' => $request->get('ElementName'),
            'LayoutId' => $request->get('LayoutId'),
            'LanguageId' => $request->get('LanguageId'),
            'Subject' => $request->get('Subject'),
            'Template' => $request->get('Template'),
            'DatabaseTable' => $request->get('DatabaseTable'),
            'TableColumn' => $request->get('TableColumn'),
            'ColumnValue' => $request->get('ColumnValue'),
        ]);
        return new ServiceDto("Email Template Created Successfully.", 200, $template);
    }

}
