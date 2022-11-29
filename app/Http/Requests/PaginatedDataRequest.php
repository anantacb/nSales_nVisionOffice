<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaginatedDataRequest extends FormRequest
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
     * * $request[
     *    "selected_columns" => string | array,
     *    "relations" => [["name" => string, columns => null | array]],
     *    "filters" => [["column"=>"column_name", "operator" => "=,!=,...", values => string | array]],
     *    "filter_by_relation" => [["relation"=>string, "column"=>"column_name", "operator" => "=,!=,...", values => string | array]],
     *    "search" => ["columns" => string | array, query=> string]
     *    "order" => [["column"=>"column_name", "sort" => "asc|desc"]]
     *    "pagination" => [page_no=>int, per_page=>int]
     * ]
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            "search_columns" => ["sometimes", "nullable", function ($attribute, $value, $fail) {
                if (!(is_array($value) || is_string($value))) {
                    $fail('The ' . $attribute . ' is invalid.');
                }
            }],
            //"relations" => "sometimes|nullable|array",
            "filters" => "sometimes|nullable|array",
            "filters.*.column" => "required",
            "filters.*.operator" => "required",
            "filters.*.values" => "required",
            /*"filter_by_relation" => "sometimes|array",
            "filter_by_relation.*.relation" => "required",
            "filter_by_relation.*.column" => "required",
            "filter_by_relation.*.operator" => "required",
            "filter_by_relation.*.values" => "required",*/
            "query" => "sometimes|nullable|string",
            "order" => "sometimes|nullable|array",
            "pagination" => "required|array:page_no,per_page"
        ];
    }
}
