<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyUserRoles extends FormRequest
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
            'CompanyUserId' => 'required|integer|exists:CompanyUser,Id',
            'RoleIds' => 'required|array|min:1',
            'RoleIds.*' => 'required|integer|exists:Role,Id',
        ];
    }
}
