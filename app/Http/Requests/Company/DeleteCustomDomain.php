<?php

namespace App\Http\Requests\Company;

use App\Models\Office\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteCustomDomain extends FormRequest
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
        $company = Company::where("Id", $this->CompanyId)->first();

        return [
            'CustomDomain' => ['required', 'string', Rule::in($company->CustomDomainsArray)],
            'HostId' => 'nullable|integer',
        ];
    }
}
