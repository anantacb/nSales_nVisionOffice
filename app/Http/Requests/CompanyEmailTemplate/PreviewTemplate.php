<?php

namespace App\Http\Requests\CompanyEmailTemplate;

use App\Models\Company\CompanyEmailLayout;
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
            'Subject' => 'required|string|max:250',
            'Template' => 'required|string',
            'LayoutId' => [
                'required',
                'exists:EmailLayout,Id',
                new ContainsYieldDirective(CompanyEmailLayout::where('Id', $this->LayoutId)->value('Template'))
            ]
        ];
    }

    public function messages()
    {
        return [
            'TemplateObject.required' => 'Template Object is required.',
            'LanguageId.required' => 'Language is required.',
            'Subject.required' => 'Subject is required.',
            'Template.required' => 'Template is required.',
            'LayoutId.required' => 'Layout is required.',
            'LayoutId.exists' => 'Layout is not exist.',
        ];
    }

}
