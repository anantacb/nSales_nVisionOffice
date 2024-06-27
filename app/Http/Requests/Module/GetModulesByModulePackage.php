<?php

namespace App\Http\Requests\Module;

use Illuminate\Foundation\Http\FormRequest;

class GetModulesByModulePackage extends FormRequest
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
            'ModulePackageId' => 'required|exists:ModulePackage,Id'
        ];
    }
}
