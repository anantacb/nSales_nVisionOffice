<?php

namespace App\Http\Requests\DataFilter;

use App\Rules\IsSafeWhereFragment;
use Illuminate\Foundation\Http\FormRequest;

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
            'Id' => 'required|exists:DataFilter,Id',

            'Name' => 'required',
            'Type' => 'required',
            'Description' => 'nullable',
            'Disabled' => 'required|boolean',

            'Value' => [
                'required_without:ValueExpression',
                'nullable',
                'string',
                'max:2000',
                'regex:/^[^;`]*$/',
                'not_regex:/(--|\/\*|\*\/)/',
                'not_regex:/\b(UNION|DROP|DELETE|INSERT|UPDATE|ALTER|TRUNCATE|EXEC|EXECUTE|GRANT|REVOKE|OUTFILE|LOAD_FILE|INFORMATION_SCHEMA|SLEEP|BENCHMARK)\b/i',
                new IsSafeWhereFragment(),
            ],
            'ValueExpression' => [
                'required_without:Value',
                'nullable',
                'string',
                'max:2000',
                'regex:/^[^;`]*$/',
                'not_regex:/(--|\/\*|\*\/)/',
                'not_regex:/\b(UNION|DROP|DELETE|INSERT|UPDATE|ALTER|TRUNCATE|EXEC|EXECUTE|GRANT|REVOKE|OUTFILE|LOAD_FILE|INFORMATION_SCHEMA|SLEEP|BENCHMARK)\b/i',
                new IsSafeWhereFragment(),
            ],

            'ApplyTo' => 'required|in:Application,Company,Role,User',
            'ModuleId' => 'required',
            'TableId' => 'required',

            'ApplicationId' => 'required_if:ApplyTo,Application',
            'CompanyId' => 'required_if:ApplyTo,Company',
            'RoleId' => 'required_if:ApplyTo,Role',
            'CompanyUserId' => 'required_if:ApplyTo,User',
        ];
    }
}
