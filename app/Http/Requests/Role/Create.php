<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'CompanyId' => 'required|exists:Company,Id',
            'Name' => [
                'required',
                Rule::unique('Role', 'Name')
                    ->where('CompanyId', $this->request->get('CompanyId'))
            ],
            'Type' => 'required',
            'Description' => 'nullable'
        ];
    }
}
