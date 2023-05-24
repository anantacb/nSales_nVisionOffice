<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignToCompany extends FormRequest
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
            'CompanyId' => 'required',
            'UserId' => 'required',
            'Initials' => [
                'required',
                Rule::unique('CompanyUser', 'Initials')
                    ->where(function ($q) {
                        $q->where('CompanyId', '=', $this->request->get('CompanyId'));
                    })
            ],
            'LicenceType' => 'required|in:NvisionMobile,NsalesOffice',
            'CultureName' => 'required',
            'Territory' => 'nullable',
            'Commission' => 'required',
            'Billable' => 'required',
            'Note' => 'nullable',

            'RoleIds' => 'required|array'
        ];
    }
}
