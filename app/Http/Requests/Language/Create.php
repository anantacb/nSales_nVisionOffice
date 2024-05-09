<?php

namespace App\Http\Requests\Language;

use Illuminate\Foundation\Http\FormRequest;

class Create extends FormRequest
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
            'Name' => 'required|unique:Language',
            'Code' => 'required|unique:Language',
            'Locale' => 'required|unique:Language',
            'IsDefault' => 'required|boolean'
        ];
    }
}
