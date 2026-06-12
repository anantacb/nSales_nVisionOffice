<?php

namespace App\Http\Requests\DefaultRole;

use App\Models\Office\Role;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Update extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'Id'   => 'required|exists:Role,Id',
            'Name' => [
                'required',
                Rule::unique('Role', 'Name')
                    ->whereNull('CompanyId')
                    ->ignore($this->request->get('Id')),
            ],
            'Description' => 'nullable',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $role = Role::find($this->input('Id'));
            if ($role && $role->CompanyId !== null) {
                $validator->errors()->add('Id', 'This role is a company role, not a default/template role.');
            }
        });
    }
}
