<?php

namespace App\Http\Requests\DefaultRole;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Create extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'Name' => [
                'required',
                Rule::unique('Role', 'Name')->whereNull('CompanyId'),
            ],
            'Type' => [
                'required',
                Rule::in([
                    'Developer', 'Administrator', 'Manager', 'Employee',
                    'Client', 'Retailer', 'WebShopViewer', 'Insights', 'Marketing',
                ]),
            ],
            'Description' => 'nullable',
        ];
    }
}
