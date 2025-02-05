<?php

namespace App\Http\Requests\ModuleSetting;

use Illuminate\Foundation\Http\FormRequest;

class DetailsOrDelete extends FormRequest
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
            'ModuleSettingId' => 'required|exists:ModuleSetting,Id'
        ];
    }
}
