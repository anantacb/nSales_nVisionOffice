<?php

namespace App\Http\Requests\CompanyEmailTemplate;

use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest
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
            'Id' => 'required|exists:mysql_company.CompanyEmailTemplate,Id',
            'LayoutId' => 'required|exists:mysql_company.CompanyEmailLayout,Id',
            'LanguageId' => 'required|exists:mysql_company.CompanyLanguage,Id',
            'Subject' => 'required|string|max:255',
            'ElementName' => 'required',
            'Template' => [
                'required',
                'string'
            ]
        ];
    }

    public function messages()
    {
        return [
            'Id.required' => 'EmailLayout Id field is required.',
            'Id.exists' => 'EmailLayout Id is not exists.',
            'LayoutId.required' => 'EmailLayoutId field is required.',
            'LayoutId.exists' => 'EmailLayoutId is not exists.',
            'ElementName.required' => 'ElementName is required.',
            'LanguageId.required' => 'Language is required.',
            'Template.required' => 'Template is required.',
        ];
    }
}
