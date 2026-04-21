<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyUserInitials extends FormRequest
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
            'CompanyUserId' => 'required|integer|exists:CompanyUser,Id',
            'CompanyId' => 'required|integer|exists:Company,Id',
            'Initials' => [
                'required',
                'string',
                Rule::unique('CompanyUser', 'Initials')
                    ->where(function ($q) {
                        $q->where('CompanyId', '=', $this->request->get('CompanyId'));
                    })
                    ->ignore($this->request->get('CompanyUserId'), 'Id'),
            ],
        ];
    }
}
