<?php

namespace App\Http\Requests\EmailTemplate;

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
    public function rules()
    {
        return [
            'LayoutId' => 'required|exists:EmailLayout,Id',
            'LanguageId' => 'required|exists:Language,Id',
            'Subject' => 'required|string|max:255',
            'Template' => [
                'required',
                'string'
            ],
//            'ElementName' => 'required',
            'ElementName' => [
                'required',
                Rule::unique('EmailTemplate')->where(function ($query) {
                    return $query->where('LanguageId', $this->LanguageId);
                }),
            ],
        ];

    }

    public function messages()
    {
        return [
            'Name.required' => 'Layout Name is required.',
            'LanguageId.required' => 'Language is required.',
            'Template.required' => 'Template is required.',
        ];
    }

}
