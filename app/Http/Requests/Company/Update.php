<?php

namespace App\Http\Requests\Company;

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
            'Id' => 'required|exists:Company,Id',
            'Name' => [
                'required',
                Rule::unique('Company', 'Name')
                    ->ignore($this->request->get('Id'))
            ],
            'Type' => 'required',
            'IntegrationType' => 'required',
            'FileTransferType' => 'required',
            'DomainName' => [
                'required',
                Rule::unique('Company', 'DomainName')
                    ->ignore($this->request->get('Id'))
            ],
            'DatabaseName' => [
                'required',
                Rule::unique('Company', 'DatabaseName')
                    ->ignore($this->request->get('Id'))
            ],
            'StorageLocation' => 'required',
            'CompanyName' => 'required',
            'Street' => 'nullable',
            'ZipCode' => 'nullable',
            'City' => 'nullable',
            'State' => 'nullable',
            'Country' => 'required',
            'VATNo' => 'nullable',
            'Email' => 'nullable',
            'PhoneNo' => 'nullable',
            'FaxNo' => 'nullable',
            'ContactPerson' => 'nullable',
            'ContactEmail' => 'nullable',
            'Homepage' => 'nullable',
            'Seats' => 'required|numeric|min:1',
            'DefaultCulture' => 'required',
            'DefaultCurrency' => 'required',
            'TrialStartDate' => 'nullable',
            'TrialDays' => 'required',
            'Disabled' => 'required',
            'Note' => 'nullable',
            'ServiceUrl' => 'required',
            'GraphQLServiceURL' => 'required',
        ];
    }
}
