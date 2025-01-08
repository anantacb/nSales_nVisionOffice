<?php

namespace App\Http\Requests\CompanyEmailTemplate;

use Illuminate\Foundation\Http\FormRequest;

class DetailsOrDelete extends FormRequest
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
            'EmailTemplateId' => 'required|exists:mysql_company.CompanyEmailTemplate,Id',
        ];
    }

    public function messages()
    {
        return [
            'EmailTemplateId.required' => 'Email Template Id field is required.',
            'EmailTemplateId.exists' => 'Email Template Id is not exists.',
        ];
    }
}
