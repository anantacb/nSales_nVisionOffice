<?php

namespace App\Http\Requests\Language;

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
            'Id' => 'required|exists:Language,Id',
            'Name' => [
                'required',
                Rule::unique('Language', 'Name')
                    ->ignore($this->request->get('Id'))
            ],
            'Code' => [
                'required',
                Rule::unique('Language', 'Code')
                    ->ignore($this->request->get('Id'))
            ],
            'Locale' => [
                'required',
                Rule::unique('Language', 'Locale')
                    ->ignore($this->request->get('Id'))
            ],
            'IsDefault' => 'required|boolean'
        ];
    }
}
