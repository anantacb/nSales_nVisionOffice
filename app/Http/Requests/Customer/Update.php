<?php

namespace App\Http\Requests\Customer;

use App\Services\TableHelper\TableHelperService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Update extends FormRequest
{
    protected TableHelperService $tableHelperService;
    protected array $overriddenRules;
    protected array $exceptColumns = ["Id", "InsertTime", "UpdateTime", "DeleteTime", "UUID", "ImportTime"];

    public function __construct(
        TableHelperService $tableHelperService,
        array              $query = [],
        array              $request = [],
        array              $attributes = [],
        array              $cookies = [],
        array              $files = [],
        array              $server = [],
                           $content = null,
    )
    {
        $this->tableHelperService = $tableHelperService;
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $baseValidationRulesArray = [
            'CompanyId' => 'required|exists:Company,Id',
        ];
        $validationRulesArray = $this->tableHelperService->getValidationArray('Customer', $this->exceptColumns, $this->request->get('CompanyId'));
        $this->overriddenRules = [
            'Id' => 'required|exists:mysql_company.Customer,Id',
            'ExportStatus' => 'required',
        ];
        return array_merge($baseValidationRulesArray, $validationRulesArray, $this->overriddenRules);
    }
}
