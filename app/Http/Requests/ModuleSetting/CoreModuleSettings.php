<?php

namespace App\Http\Requests\ModuleSetting;

use App\Rules\ModuleSettingDataType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CoreModuleSettings extends FormRequest
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
            'Module' => 'required|exists:Module,Name',
            'SettingKeys' => 'required|array',
            'SettingKeys.*' => 'required||exists:ModuleSetting,Name',
        ];
    }
}
