<?php

namespace App\Http\Requests\Database;

use Illuminate\Foundation\Http\FormRequest;

class CopyDB extends FormRequest
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
            'selectedDatabases' => 'required|array',

//            'selectedCompanies' => 'required|exists:mysql.Company,Id',
//            'name' => 'required|unique:mysql.Table,Name',
//            'type' => 'required|in:Server,Client,Both',
//            'database' => 'required|in:Company,Office,Both',
//            'module' => 'required',
//            'selectedCompanies' => 'sometimes|array',
//            'disabled' => 'required|boolean',
//            'clientSync' => 'nullable|in:Download,Both',
//            'autoNumbering' => 'required|boolean',
//            'enableTruncate' => 'required|boolean',
//            'sqlTruncate' => 'nullable',
//            'sqlSeed' => 'nullable',
//            'note' => 'nullable'
        ];
    }
}
