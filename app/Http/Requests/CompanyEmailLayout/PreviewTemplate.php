<?php

namespace App\Http\Requests\CompanyEmailLayout;

use App\Rules\ContainsYieldDirective;
use Illuminate\Foundation\Http\FormRequest;

class PreviewTemplate extends FormRequest
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
            'TemplateObject' => 'required|array',
            'LanguageId' => 'required|exists:mysql_company.CompanyLanguage,Id',
            'Template' => [
                'required',
                'string',
                new ContainsYieldDirective($this->Template)
            ]
        ];
    }

    public function messages()
    {
        return [
            'LanguageId.required' => 'Language is required.',
            'Template.required' => 'Template is required.',
            'TemplateObject.required' => 'Template Object is required.',
        ];
    }

}
