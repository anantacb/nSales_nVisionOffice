<?php

namespace App\Http\Requests\Table;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTable extends FormRequest
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
            'Id' => 'required|exists:mysql.Table,Id',
            'Disabled' => 'required|boolean',
            'ClientSync' => 'nullable|in:Download,Both',
            'AutoNumbering' => 'required|boolean',
            'EnableSqlTruncate' => 'required|boolean',
            'SqlTruncate' => 'nullable',
            'SqlSeed' => 'nullable',
            'Note' => 'nullable'
        ];
    }
}
