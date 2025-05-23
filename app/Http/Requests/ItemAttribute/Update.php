<?php

namespace App\Http\Requests\ItemAttribute;

use Illuminate\Foundation\Http\FormRequest;

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
            'ItemId' => 'required|exists:mysql_company.Item,Id',
            'ItemNumber' => 'required',
            'ItemAttributes' => 'required|array',
            'ItemAttributes.*.Id' => 'nullable',
            'ItemAttributes.*.TypeCode' => 'required',
            'ItemAttributes.*.Value' => 'nullable',
            'ItemAttributes.*.Language' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'ItemAttributes.*.TypeCode.required' => 'Attribute field is required.',
            'ItemAttributes.*.Value.required' => 'Value field is required.',
            'ItemAttributes.*.Language.required' => 'Language is required.'
        ];
    }

}
