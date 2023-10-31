<?php

namespace App\Http\Requests\TableHelper;

use Illuminate\Foundation\Http\FormRequest;

class GetColumnDistinctValues extends FormRequest
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
            'DatabaseType' => 'required|in:Office,Company',
            'CompanyId' => 'required_if:database_type,Company',
            'TableName' => 'required',
            'ColumnName' => 'required',
        ];
    }
}
