<?php

namespace App\Http\Requests\Customer;

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
    public function rules(): array
    {
        return [
            'Id' => 'required|exists:Role,Id',
            'CompanyId' => 'required',
            'Name' => [
                'required',
                Rule::unique('Role', 'Name')
                    ->where('CompanyId', $this->request->get('CompanyId'))
                    ->ignore($this->request->get('Id'))
            ]
        ];
    }
}
