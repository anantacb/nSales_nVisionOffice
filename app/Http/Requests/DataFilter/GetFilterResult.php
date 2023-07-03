<?php

namespace App\Http\Requests\DataFilter;

use Illuminate\Foundation\Http\FormRequest;

class GetFilterResult extends FormRequest
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
            'DataFilterId' => 'required|exists:DataFilter,Id',
            'CompanyUserId' => 'required',
        ];
    }
}
