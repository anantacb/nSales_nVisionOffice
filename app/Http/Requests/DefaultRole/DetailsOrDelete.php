<?php

namespace App\Http\Requests\DefaultRole;

use App\Models\Office\Role;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class DetailsOrDelete extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'RoleId' => 'required|integer|exists:Role,Id',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $role = Role::find($this->input('RoleId'));
            if ($role && $role->CompanyId !== null) {
                $validator->errors()->add('RoleId', 'This role is a company role, not a default/template role.');
            }
        });
    }
}
