<?php

namespace App\Http\Requests\EmailLayout;

use Illuminate\Foundation\Http\FormRequest;

class DetailsOrDelete extends FormRequest
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
            'EmailLayoutId' => 'required|exists:EmailLayout,Id',
        ];
    }

    public function messages()
    {
        return [
            'EmailLayoutId.required' => 'EmailLayout Id field is required.',
            'EmailLayoutId.exists' => 'EmailLayout Id is not exists.',
        ];
    }
}
