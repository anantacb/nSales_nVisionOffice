<?php

namespace App\Http\Requests\EmailConfiguration;

use Illuminate\Foundation\Http\FormRequest;

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
            'Name' => 'required',
            'TemplateType' => 'required',
            'Disabled' => 'required|boolean',
            'From' => 'nullable',
            'To' => 'nullable',
            'Cc' => 'nullable',
            'Bcc' => 'nullable',
            'SendToCompany' => 'required|boolean',
            'SendToUser' => 'required|boolean',
            'SendToCustomer' => 'required|boolean',
            'SendToSupplier' => 'required|boolean',
            'SendToEmployee' => 'required|boolean',
            'Subject' => 'nullable',
            'Body' => 'nullable',
            'Description' => 'nullable',
            'TemplatePath' => 'nullable',

            'ApplyTo' => 'required|in:Application,Company,Role,User',
            'ModuleId' => 'required',

            'ApplicationId' => 'required_if:ApplyTo,Application',
            'CompanyId' => 'required_if:ApplyTo,Company',
            'RoleId' => 'required_if:ApplyTo,Role',
            'CompanyUserId' => 'required_if:ApplyTo,User',
        ];
    }
}
