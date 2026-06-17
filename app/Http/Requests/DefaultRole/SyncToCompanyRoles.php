<?php

namespace App\Http\Requests\DefaultRole;

use Illuminate\Foundation\Http\FormRequest;

class SyncToCompanyRoles extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'Id' => 'required|integer|exists:Role,Id',
        ];
    }
}
