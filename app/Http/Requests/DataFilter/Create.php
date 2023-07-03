<?php

namespace App\Http\Requests\DataFilter;

use Illuminate\Foundation\Http\FormRequest;

class Create extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
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
            'Name' => 'required',
            'Type' => 'required',
            'Description' => 'nullable',
            'Disabled' => 'required|boolean',


            'Value' => 'required_without:ValueExpression',
            'ValueExpression' => 'required_without:Value',

            'ApplyTo' => 'required|in:Application,Company,Role,User',
            'ModuleId' => 'required',
            'TableId' => 'required',

            'ApplicationId' => 'required_if:ApplyTo,Application',
            'CompanyId' => 'required_if:ApplyTo,Company',
            'RoleId' => 'required_if:ApplyTo,Role',
            'CompanyUserId' => 'required_if:ApplyTo,User',
        ];
    }
}
