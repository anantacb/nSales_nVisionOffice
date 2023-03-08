<?php

namespace App\Http\Requests\Module;

use Illuminate\Foundation\Http\FormRequest;

class Create extends FormRequest
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
            'ModuleId' => 'required|exists:Module,Id',
            'Name' => 'required|unique:Module,Name',
            'Description' => 'nullable',
            'Note' => 'nullable',
            'Type' => 'required|in:Core,Root,Package,Standard,Extension',
            'Disabled' => 'required|boolean',
            'SyncOfficeData' => 'required|boolean',
            'ViewPath' => 'nullable',
            'MainTableName' => 'nullable',
            'IsGenericModule' => 'required|boolean',
        ];
    }
}
