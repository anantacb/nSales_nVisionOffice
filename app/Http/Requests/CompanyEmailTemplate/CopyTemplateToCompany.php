<?php

namespace App\Http\Requests\CompanyEmailTemplate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CopyTemplateToCompany extends FormRequest
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
    public function rules()
    {
        return [
            'ElementName' => [
                'required',
                Rule::unique('mysql_company.CompanyEmailTemplate')->where(function ($query) {
                    return $query->where('LanguageId', $this->LanguageId);
                }),
            ],
            'LanguageId' => 'required|exists:mysql_company.CompanyLanguage,Id',
            'LayoutId' => 'required|exists:mysql_company.CompanyEmailLayout,Id',
            'Subject' => 'required',
            'Template' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'ElementName.required' => 'Element Name is required.',
            'LanguageId.required' => 'Language is required.',
            'LayoutId.required' => 'Layout is required.',
            'Subject.required' => 'Email Subject is required.',
            'Template.required' => 'Email Template is required.',
        ];
    }

}
