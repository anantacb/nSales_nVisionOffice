<?php

namespace App\Http\Requests\Company;

use App\Rules\ValidCname;
use Illuminate\Foundation\Http\FormRequest;

class AddCustomDomain extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
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
            'UUID' => 'required|uuid',
//            'CustomDomain' => ['required', 'string', new ValidCname()],
            'CustomDomain' => ['required', 'string'],
        ];
    }
}
