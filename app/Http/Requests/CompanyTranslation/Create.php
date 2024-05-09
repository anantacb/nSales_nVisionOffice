<?php

namespace App\Http\Requests\CompanyTranslation;

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
    public function rules(): array
    {
        return [
            'CompanyLanguageId' => 'required|exists:mysql_company.CompanyLanguage,Id',
            'Type' => 'required',
            'ElementName' => [
                'required',
                Rule::unique('mysql_company.CompanyTranslation', 'ElementName')->where(function ($query) {
                    $query->where('CompanyLanguageId', $this->input('CompanyLanguageId'))
                        ->where('Type', $this->input('Type'));
                })
            ],
            'Translations' => 'required|array'
        ];
    }
}
