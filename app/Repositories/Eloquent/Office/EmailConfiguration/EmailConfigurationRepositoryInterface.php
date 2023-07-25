<?php

namespace App\Repositories\Eloquent\Office\EmailConfiguration;


use App\Repositories\Eloquent\Base\BaseRepositoryInterface;

interface EmailConfigurationRepositoryInterface extends BaseRepositoryInterface
{

    /**
     * Returns paginated data.
     * Request Validated by PaginatedDataRequest
     * $request[
     *    "selected_columns" => string | array,
     *    "relations" => [["name" => string, "columns" => null | array]],
     *    "filters" => [["column"=>"column_name", "operator" => "=,!=,...", "values" => string | array]],
     *    "search" => ["columns" => string | array, "query" => string]
     *    "order" => [["column"=>"column_name", "sort" => "asc|desc"]]
     *    "pagination" => ["page_no"=>int, "per_page"=>int]
     * ]
     */
    public function paginatedCompanyWiseData(array $request);
}
