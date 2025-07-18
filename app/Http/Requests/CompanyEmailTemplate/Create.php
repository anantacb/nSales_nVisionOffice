<?php

namespace App\Http\Requests\CompanyEmailTemplate;

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
            'ElementName' => [
                'required',
                Rule::unique('mysql_company.CompanyEmailTemplate')
                    ->where(function ($query) {
                        return $query->where('LanguageId', $this->LanguageId)
                            ->where('DatabaseTable', $this->DatabaseTable)
                            ->where('TableColumn', $this->TableColumn)
                            ->where('ColumnValue', $this->ColumnValue);
                    }),
            ],
            'LayoutId' => 'required|exists:mysql_company.CompanyEmailLayout,Id',
            'LanguageId' => 'required|exists:mysql_company.CompanyLanguage,Id',
            'Subject' => 'required|string|max:255',
            'Template' => [
                'required',
                'string'
            ],

            'DatabaseTable' => 'nullable',
            'TableColumn' => 'required_with:DatabaseTable',
            'ColumnValue' => 'required_with:DatabaseTable',
        ];
    }

    public function messages()
    {
        return [
            'ElementName.required' => 'Layout Name is required.',
            'ElementName.unique' => 'ElementName is not unique with Language, Table, TableColumn, ColumnValue.',
            'LanguageId.required' => 'Language is required.',
            'Template.required' => 'Template is required.',
        ];
    }

}
