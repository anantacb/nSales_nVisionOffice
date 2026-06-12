<?php

namespace App\Http\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;

class Create extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'Aliases' => 'required|string|max:191|unique:Permission,Aliases',
            'Name' => 'required|string|max:191',
            'ModuleId' => 'nullable|integer|exists:Module,Id',
            'Description' => 'nullable|string',
            'IsDeveloperOnly' => 'required|boolean',
        ];
    }
}
