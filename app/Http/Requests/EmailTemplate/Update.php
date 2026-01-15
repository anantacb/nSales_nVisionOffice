<?php

namespace App\Http\Requests\EmailTemplate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'Id' => 'required|exists:EmailTemplate,Id',
            'LayoutId' => 'required|exists:EmailLayout,Id',
            'LanguageId' => 'required|exists:Language,Id',
            'Subject' => 'required|string|max:255',
            'Template' => 'required|string',

            'ElementName' => [
                'required',
                Rule::unique('EmailTemplate')
                    ->where(function ($query) {
                        return $query->where('LanguageId', $this->LanguageId)
                            ->where('DatabaseTable', $this->DatabaseTable)
                            ->where('TableColumn', $this->TableColumn)
                            ->where('ColumnValue', $this->ColumnValue)
                            ->where('Id', '<>', $this->Id);
                    }),
            ],
            'DatabaseTable' => 'nullable',
            'TableColumn' => 'required_with:DatabaseTable',
            'ColumnValue' => 'required_with:DatabaseTable',
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
            'ElementName.unique' => 'ElementName is not unique with Language, Table, TableColumn, ColumnValue.',
            'LanguageId.required' => 'Language is required.',
            'Template.required' => 'Template is required.',
        ];
    }
}
