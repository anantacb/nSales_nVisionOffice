<?php

namespace App\Http\Requests\Translation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'LanguageId' => 'required|exists:Language,Id',
            'Type' => 'required',
            'ElementName' => [
                'required',
                Rule::unique('Translation', 'ElementName')->where(function ($query) {
                    $query->where('LanguageId', $this->input('LanguageId'))
                        ->where('Type', $this->input('Type'));
                })
            ],
            'Translations' => 'required|array'
        ];
    }
}
