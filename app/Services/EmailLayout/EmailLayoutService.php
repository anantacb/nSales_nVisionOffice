<?php

namespace App\Services\EmailLayout;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\EmailLayout\EmailLayoutRepositoryInterface;
use App\Repositories\Eloquent\Office\Translation\TranslationRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

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

    public static function renderTemplateOld($template, $data): array
    {
        try {
            // Extract data to create local variables
            extract($data);
            // Start output buffering
            ob_start();
            // Evaluate the template
            eval('?>' . $template);

            // Get the rendered content and clean the buffer
            return [
                'template' => ob_get_clean(),
                'status' => true
            ];

        } catch (Throwable $exception) {
            // Clean the output buffer to prevent partial content
            if (ob_get_level() > 0) {
                ob_end_clean();
            }

            Log::error("Unable to render: " . $exception->getMessage());
            return [
                'template' => '',
                'status' => false
            ];
        }
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

//        $renderedTemplate = Blade::render($fullTemplate, $data);
//
//        // Get the compiled view path
//        $compiledPath = Blade::getCompiledPath(md5($renderedTemplate));
//
//        // Purge the compiled file after rendering
//        if (File::exists($compiledPath)) {
//            dd($compiledPath);
//            File::delete($compiledPath);
//        }
        // Render the subject
        $renderedSubject = $this->renderTemplate($emailSubject, $data);

        // Render the template
        $renderedTemplate = $this->renderTemplate($fullTemplate, $data);

        return [
           'subject' => $renderedSubject,
            'template' => $renderedTemplate,
        ];



//        try {
//            // Render the subject
//            $renderedSubject = $this->renderTemplate($emailSubject, $data);
////            $renderedSubject = Blade::render($emailSubject, $data);
////            $compiledSubject = Blade::compileString($emailSubject);
////            $renderedSubjectObj = self::renderTemplate($compiledSubject, $data);
////            $renderedSubject = $renderedSubjectObj['status'] ? $renderedSubjectObj['template'] : "";
//
//            // Render the template
//            $renderedTemplate = $this->renderTemplate($fullTemplate, $data);
//
////            $compiledTemplate = Blade::compileString($fullTemplate);
////            $renderedTemplateObj = self::renderTemplate($compiledTemplate, $data);
////            $renderedTemplate = $renderedTemplateObj['status'] ? $renderedTemplateObj['template'] : "";
//
//            return [
//                'subject' => $renderedSubject,
//                'template' => $renderedTemplate,
//            ];
//
//        } catch (Throwable $exception) {
//            Log::error("Unable to compile: " . $exception->getMessage());
//            return [
//                'subject' => '',
//                'template' => '',
//            ];
//        }
    }

    public function renderTemplate($template, $data): string
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
//            ['column' => 'LanguageId', 'operand' => '=', 'value' => $request->get('LanguageId')]
//        ]);
        $this->layoutRepository->findByIdAndDelete($request->get('LanguageId'));
        return new ServiceDto("Layout Deleted Successfully.", 200);
    }

}
