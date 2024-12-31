<?php

namespace App\Services\EmailLayout;

use Exception;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class EmailHelperService
{

    /**
     * @param string $layout
     * @param string $emailTemplate
     * @param string $emailSubject
     * @param array $data
     * @return array|string[]
     * @throws Exception
     *  TO DO:: Move to Helper
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
     * TO DO:: Move to Helper
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

    public function getEventProperties($fields): array
    {
        $properties = [];
        foreach ($fields as $field) {
            $properties[$field['Field']] = $field['Name'];
        }
        return $properties;
    }


}
