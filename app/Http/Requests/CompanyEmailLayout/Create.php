<?php

namespace App\Http\Requests\CompanyEmailLayout;

use App\Rules\ContainsYieldDirective;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Create extends FormRequest
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
            'LanguageId' => 'required|exists:mysql_company.CompanyLanguage,Id',
            'Name' => [
                'required',
                Rule::unique('mysql_company.CompanyEmailLayout')->where(function ($query) {
                    return $query->where('LanguageId', $this->LanguageId);
                }),
            ],
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
            'Name.required' => 'Layout Name is required.',
            'LanguageId.required' => 'Language is required.',
            'Template.required' => 'Template is required.',
        ];
    }

}
