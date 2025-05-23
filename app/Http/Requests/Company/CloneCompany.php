<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class CloneCompany extends FormRequest
{
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
        return [
            'SourceCompanyId' => 'required|exists:Company,Id',
            'Name' => 'required|unique:Company',
            'DomainName' => 'required|unique:Company',
            'DatabaseName' => 'required|unique:Company',
            'CompanyName' => 'required',
            'WithRolesAndUsers' => 'required|boolean',
            'WithSettings' => 'required|boolean',
            'WithData' => 'required|boolean',
            'WithDataFilters' => 'required|boolean',
            'WithEmailLayoutsAndTemplates' => 'required|boolean',
            'WithEmailConfigurations' => 'required|boolean',
            'WithLanguageAndTranslations' => 'required|boolean',
        ];
    }
}
