<?php

namespace App\Services\EmailTemplate;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\EmailTemplate\EmailTemplateRepositoryInterface;
use App\Repositories\Eloquent\Office\Translation\TranslationRepositoryInterface;
use App\Services\ModuleSetting\ModuleSettingServiceInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmailTemplateService implements EmailTemplateServiceInterface
{
    protected EmailTemplateRepositoryInterface $templateRepository;
    protected TranslationRepositoryInterface $translationRepository;

    public function __construct(
        EmailTemplateRepositoryInterface $templateRepository,
        ModuleSettingServiceInterface    $moduleSettingService,
        TranslationRepositoryInterface   $translationRepository
    )
    {
        $this->templateRepository = $templateRepository;
        $this->moduleSettingService = $moduleSettingService;
        $this->translationRepository = $translationRepository;
    }

    public function getEmailTemplates(Request $request): ServiceDto
    {
        $request = $request->all();
        $request['relations'] = [
            [
                "name" => "language", "columns" => ['Id', 'Name']
            ]
        ];
        $layouts = $this->templateRepository->paginatedData($request);
        return new ServiceDto("Templates retrieved!!!", 200, $layouts);
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
            $emailEvents[$key]['templateObject'] = $this->getEventProperties(array_merge($layoutFields, $emailEvent['Fields']));
        }

        return $emailEvents;
    }

    public function getEventProperties($fields): array
    {
        $properties = [];
        foreach ($fields as $field) {
            $properties[$field['Field']] = $field['Name'];
        }
        return $properties;
    }

    /**
     * @throws Exception
     */
    public function getDataForPreview(Request $request): ServiceDto
    {
        // Prepare the data to be passed to the template
        $data = [
            'ProductName' => 'test',
            'ProductUrl' => 'test',
            'company_name' => 'company_name',    //$selectedCompany->module_settings['WebShop']['CompanyName'],
            'CompanyName' => 'CompanyName',    //$selectedCompany->module_settings['WebShop']['CompanyName'],
            'ShopName' => 'ShopName',    // $selectedCompany->module_settings['WebShop']['ShopTitle'],
            'ShopLink' => 'ShopLink',    // $selectedCompany->module_settings['WebShop']['PublicUrl'],
            'CompanyStreet' => 'CompanyStreet',    // $selectedCompany->Street,
            'CompanyZipCode' => 'CompanyZipCode',    // $selectedCompany->ZipCode,
            'CompanyCity' => 'CompanyCity',    // $selectedCompany->City,
            'CompanyEmail' => 'CompanyEmail',    // $selectedCompany->Email,
            'CompanyAddress' => 'CompanyAddress',    // $selectedCompany->Street . ', ' . $selectedCompany->ZipCode . ', ' . $selectedCompany->City,
            'Name' => 'test Name',
            'Login' => 'test Login',
            'Password' => 'test Password',
        ];

        $preview = $this->renderTemplateAndSubject(
            $request['Template'],
            "Content Goes here",
            "",
            $data
        );

        return new ServiceDto("Preview data retrieved Successfully.", 200, $preview);
    }

    /**
     * @param string $layout
     * @param string $emailTemplate
     * @param string $emailSubject
     * @param array $data
     * @return array|string[]
     * @throws Exception
     */
    public function renderTemplateAndSubject(string $layout, string $emailTemplate, string $emailSubject, array $data): array
    {
        // Ensure the layout contains @yield('content')
        if (!str_contains($layout, "@yield('content')")) {
            throw new Exception("The layout does not contain a @yield('content') directive.");
        }

        // Replace the @yield('content') in the layout
        $fullTemplate = str_replace("@yield('content')", $emailTemplate, $layout);

        // Render the subject
        $renderedSubject = $this->renderTemplate($emailSubject, $data);

        // Render the template
        $renderedTemplate = $this->renderTemplate($fullTemplate, $data);

        return [
            'subject' => $renderedSubject,
            'template' => $renderedTemplate,
        ];

    }

    /**
     * @param string $template
     * @param array $data
     * @return string
     */
    public function renderTemplate(string $template, array $data): string
    {
        try {
            $renderedTemplate = Blade::render($template, $data);

            // Purge the compiled file after rendering
            $compiledPath = Blade::getCompiledPath(md5($renderedTemplate));
            if (File::exists($compiledPath)) {
                File::delete($compiledPath);
                Log::info("Deleted compiled file: $compiledPath");
            }

            return $renderedTemplate;

        } catch (Throwable $exception) {
            Log::error("Unable to render: " . $exception->getMessage());
            return "";
        }

    }

    public function delete(Request $request): ServiceDto
    {
//        $this->translationRepository->deleteByAttributes([
//            ['column' => 'LanguageId', 'operand' => '=', 'value' => $request->get('EmailTemplateId')]
//        ]);
        $this->templateRepository->findByIdAndDelete($request->get('EmailTemplateId'));
        return new ServiceDto("Template Deleted Successfully.", 200);
    }

}
