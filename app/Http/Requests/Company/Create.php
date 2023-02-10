<?php

namespace App\Http\Requests\Company;

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
            'Name' => 'required|unique:Company',
            'Type' => 'required',
            'IntegrationType' => 'required',
            'FileTransferType' => 'required',
            'DomainName' => 'required|unique:Company',
            'DatabaseName' => 'required|unique:Company',
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
            'Seats' => 'required',
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
