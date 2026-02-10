<?php

namespace App\Services\EmailLayout;

use App\Models\Office\Table;
use App\Repositories\Eloquent\Office\TableField\TableFieldRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

abstract class EmailHelperService
{
    protected array $hiddenTableFields = ["Id", "InsertTime", "UpdateTime", "DeleteTime", "ImportTime", "ExportTime",
        "InsertBy", "UpdateBy", "DeleteBy", "ImportBy", "ExportBy"];

    protected TableFieldRepositoryInterface $tableFieldRepository;

    public function __construct($tableFieldRepository)
    {
        $this->tableFieldRepository = $tableFieldRepository;
    }

    public static function getCompanyDataForTemplate(): array
    {
        $selectedCompany = Cache::get('company_' . request()->get('CompanyId'));

        return [
            'CompanyName' => $selectedCompany->CompanyName,
            'CompanyStreet' => $selectedCompany->Street,
            'CompanyZipCode' => $selectedCompany->ZipCode,
            'CompanyCity' => $selectedCompany->City,
            'CompanyPhone' => $selectedCompany->PhoneNo,
            'CompanyEmail' => $selectedCompany->Email,
            'CompanyFax' => $selectedCompany->FaxNo,
            'CompanyVatNo' => $selectedCompany->VATNo,
            'CompanyState' => $selectedCompany->State,
            'CompanyAddress' => $selectedCompany->Street . ', ' . $selectedCompany->ZipCode . ', ' . $selectedCompany->City,
            'CompanyCountry' => $selectedCompany->Country,
            'CompanyLogoUrl' => isset($selectedCompany['imageHostAccount']) ?
                $selectedCompany['imageHostAccount']['Home'] . '/logo.png' : '',
        ];
    }

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

    /**
     * @param array $layoutFields
     * @param array $emailEvent
     * @param null $companyId
     * @return array
     */
    public function fetchFieldsData(array $layoutFields, array $emailEvent, $companyId = null): array
    {
        $fields = array_merge($layoutFields, $this->getEventProperties($emailEvent['Fields'] ?? []));
        return $this->fetchFields($fields, $emailEvent, $companyId);
    }

    /**
     * @param array $fields
     * @return array
     */
    public function getEventProperties(array $fields): array
    {
        $properties = [];

        foreach ($fields as $field) {
            $properties[$field['Field']] = $field['Name'];
        }

        return $properties;
    }

    /**
     * @param array $fields
     * @param array $emailEvent
     * @param null $companyId
     * @return array
     */
    public function fetchFields(array $fields, array $emailEvent, $companyId = null): array
    {
        // Fetch fields from main table
        if (isset($emailEvent['Table'])) {
            $tableFields = $this->fetchTableFields($emailEvent['Table'], $companyId);
            $fields = array_merge($tableFields, $fields);
        }

        // Handle children recursively
        if (!empty($emailEvent['Children']) && is_array($emailEvent['Children'])) {
            foreach ($emailEvent['Children'] as $child) {
                $fields = $this->assignRelation($fields, $child, $companyId);
            }
        }

        return $fields;
    }

    /**
     * @param string $tableName
     * @param $companyId
     * @return array
     */
    public function fetchTableFields(string $tableName, $companyId = null): array
    {
        $fields = [];
        $tableId = Table::where('Name', $tableName)->value('Id');

        if (!$tableId) {
            return $fields;
        }

        // Fetch general table fields
        $tableFields = $this->tableFieldRepository->getGeneralTableFields($tableId);

        // Fetch company-specific fields only if $companyId is provided
        if ($companyId) {
            $companySpecificFields = $this->tableFieldRepository->getCompanySpecificTableFields($tableId, $companyId);
            $tableFields = $tableFields->merge($companySpecificFields);
        }

        foreach ($tableFields as $tableField) {
            if (!in_array($tableField->Name, $this->hiddenTableFields)) {
                $fields[$tableField->Name] = $tableField->Name;
            }
        }

        return $fields;
    }

    /**
     * @param array $fields
     * @param array $child
     * @param $companyId
     * @return array
     *  Assign child elements based on relation type (HasMany or BelongsTo).
     */
    public function assignRelation(array &$fields, array $child, $companyId = null): array
    {
        $childFieldsData = $this->processChild($child, $companyId);

        $relation = $child['Relation'] ?? "BelongsTo";
        $relationKey = $relation === "HasMany" ? Str::plural($child['Name']) : $child['Name'];

        if ($relation === "HasMany") {
            $fields[$relationKey] = [$childFieldsData];
        } else {
            $fields[$relationKey] = $childFieldsData;
        }

        return $fields;
    }

    /**
     * @param array $child
     * @param $companyId
     * @return array
     *  Recursively process children and assign them based on their relation.
     */
    public function processChild(array $child, $companyId = null): array
    {
        $childFields = $this->getEventProperties($child['Fields'] ?? []);
        return $this->fetchFields($childFields, $child, $companyId);
    }

}
