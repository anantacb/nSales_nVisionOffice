<?php

namespace App\Http\Requests\ModulePackage;

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
            'Id' => 'required|exists:ModulePackage,Id',
            'Name' => [
                'required',
                Rule::unique('ModulePackage', 'Name')
                    ->ignore($this->request->get('Id'))
            ],
            'Type' => 'required|in:Package,CompanySetup,MenuGroup'
        ];
    }
}
