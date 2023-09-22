<?php

namespace App\Http\Requests\ApplicationModule;

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
            'Id' => 'required|exists:ApplicationModule,Id',
            'ApplicationId' => 'required|exists:Application,Id',
            'ModuleId' => [
                'required',
                Rule::unique('ApplicationModule', 'ModuleId')
                    ->where('ApplicationId', $this->request->get('ApplicationId'))
                    ->ignore($this->request->get('Id'))
            ],
            'AlwaysEnabled' => 'required|boolean',
            'ApplicationVersionStart' => 'nullable',
            'ApplicationVersionEnd' => 'nullable',
            'Title' => 'nullable',
            'SubTitle' => 'nullable',
            'Description' => 'nullable',
        ];
    }
}
