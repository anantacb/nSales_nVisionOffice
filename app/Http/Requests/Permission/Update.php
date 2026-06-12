<?php

namespace App\Http\Requests\Permission;

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
            'Id' => 'required|integer|exists:Permission,Id',
            'Aliases' => [
                'required',
                'string',
                'max:191',
                Rule::unique('Permission', 'Aliases')->ignore($this->input('Id'), 'Id'),
            ],
            'Name' => 'required|string|max:191',
            'ModuleId' => 'nullable|integer|exists:Module,Id',
            'Description' => 'nullable|string',
            'IsDeveloperOnly' => 'required|boolean',
        ];
    }
}
