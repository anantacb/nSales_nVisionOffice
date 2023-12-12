<?php

namespace App\Repositories\Eloquent\Company\CustomerVisit;

use App\Models\Company\CustomerVisit;
use App\Repositories\Eloquent\Base\BaseRepository;

class CustomerVisitRepository extends BaseRepository implements CustomerVisitRepositoryInterface
{
    public function __construct(CustomerVisit $model)
    {
        parent::__construct($model);
    }

    /**
     * Returns paginated data.
     * Request Validated by PaginatedDataRequest
     * $request[
     *    "selected_columns" => string | array,
     *    "relations" => [["name" => string, "columns" => null | array]],
     *    "filters" => [["column"=>"column_name", "operator" => "=,!=,...", "values" => string | array]],
     *    "filter_by_relation" => [["relation"=>string, "column"=>"column_name", "operator" => "=,!=,...", "values" => string | array]],
     *    "search" => ["columns" => string | array, "query" => string]
     *    "order" => [["column"=>"column_name", "sort" => "asc|desc"]]
     *    "pagination" => ["page_no"=>int, "per_page"=>int]
     * ]
     */
    public function paginatedData(array $request)
    {
        $query = $this->model;

        // Select only selected columns
        $query = $this->getSelectedColumns($request, $query);

        // Load relation with selected columns
        $query = $this->getRelations($request, $query);

        // Filter by table column   getFilters
        $query = $this->getFilters($request, $query);

        if (isset($request["filters"]) && count($request["filters"]) > 0) {
            foreach ($request["filters"] as $filter) {
                if ($filter["column"] === "DateStart"){
                    $query = $query->whereBetween($filter["column"], [$filter["values"] . " 00:00:00", $filter["values"] . " 23:59:59"]);
                } elseif (is_array($filter["values"])) {
                    if ($filter["operator"] === "!=") {
                        $query = $query->whereNotIn($filter["column"], $filter["values"]);
                    } else {
                        $query = $query->whereIn($filter["column"], $filter["values"]);
                    }
                } else {
                    $query = $query->where($filter["column"], $filter["operator"], $filter["values"]);
                }
            }
        }

        // Filter by relation
        $query = $this->getFilterByRelation($request, $query);

        // Search
        $query = $this->getSearch($request, $query);

        // Order
        $query = $this->getOrder($request, $query);

        // pagination
        return $this->getPagination($request, $query);
    }
}
