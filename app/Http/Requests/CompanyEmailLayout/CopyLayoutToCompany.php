<?php

namespace App\Http\Requests\CompanyEmailLayout;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CopyLayoutToCompany extends FormRequest
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
            'Name' => [
                'required',
                Rule::unique('mysql_company.CompanyEmailLayout')->where(function ($query) {
                    return $query->where('LanguageId', $this->LanguageId);
                }),
            ],
            'LanguageId' => 'required|exists:mysql_company.CompanyLanguage,Id',
            'Template' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'Name.required' => 'Element Name is required.',
            'LanguageId.required' => 'Language is required.',
            'Template.required' => 'Email Template is required.',
        ];
    }

}
