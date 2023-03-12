<?php

namespace App\Http\Requests\ModuleSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Update extends FormRequest
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
            'Id' => 'required|exists:ModuleSetting,Id',
            'ModuleId' => 'required|exists:Module,Id',
            'Name' => [
                'required',
                Rule::unique('ModuleSetting', 'Name')
                    ->where('ModuleId', $this->request->get('ModuleId'))
                    ->ignore($this->request->get('Id'))
            ],
            'DataType' => [
                'required',
                Rule::in([
                    'Boolean', 'Double', 'Int32', 'String',
                    "regex:^Enum\((('\w+'),*)+\)$"
                ])
            ],
            'Options' => 'nullable',
            'Disabled' => 'required|boolean',
            'CoreSetting' => 'required|boolean',
            'Readonly' => 'required|boolean',
            'Visible' => 'required|boolean',
            'Value' => 'nullable',
            'ValueExpression' => 'nullable',
            'Note' => 'nullable',
        ];
    }
}
