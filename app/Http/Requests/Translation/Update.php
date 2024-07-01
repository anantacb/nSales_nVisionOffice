<?php

namespace App\Http\Requests\Translation;

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
            'Id' => 'required|exists:Translation,Id',
            'LanguageId' => 'required|exists:Language,Id',
            'Type' => 'required',
            'ElementName' => [
                'required',
                Rule::unique('Translation', 'ElementName')
                    ->where('LanguageId', $this->input('LanguageId'))
                    ->ignore($this->input('Id'))
            ],
            'Translations' => 'required|array'
        ];
    }
}
