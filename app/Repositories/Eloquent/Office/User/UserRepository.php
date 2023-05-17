<?php

namespace App\Repositories\Eloquent\Office\User;

use App\Models\Office\User;
use App\Repositories\Eloquent\Base\BaseRepository;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
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
     *    "search" => ["columns" => string | array, "query" => string]
     *    "order" => [["column"=>"column_name", "sort" => "asc|desc"]]
     *    "pagination" => ["page_no"=>int, "per_page"=>int]
     * ]
     */
    public function paginatedNonDevelopersData(array $request)
    {
        $query = $this->model;

        // Select only selected columns
        if (isset($request["selected_columns"]) && $request["selected_columns"]) {
            $query = $query->select($request["selected_columns"]);
        }

        // Load relation with selected columns
        if (isset($request["relations"]) && count($request["relations"]) > 0) {
            foreach ($request['relations'] as $relation) {
                if (isset($relation["columns"]) && is_array($relation["columns"]) && count($relation["columns"]) > 0) {
                    $query = $query->with($relation["name"] . ":" . implode(",", $relation["columns"]));
                } else {
                    $query = $query->with($relation["name"]);
                }
            }
        }

        // Filter by table column
        if (isset($request["filters"]) && count($request["filters"]) > 0) {
            foreach ($request["filters"] as $filter) {
                if (is_array($filter["values"])) {
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
        $query = $query->whereHas('companyUser', function ($q) use ($request) {
            $q->whereHas('roles', function ($q1) {
                $q1->where('Type', '!=', 'Developer');
            })->where('CompanyId', $request['CompanyId']);
        });

        // Search
        if (isset($request["query"]) && $request["query"] && isset($request["search_columns"])) {
            if (is_array($request["search_columns"])) {
                $query = $query->where(function ($query) use ($request) {
                    foreach ($request["search_columns"] as $key => $column) {
                        if ($key === 0) {
                            $query = $query->where($column, "like", "%" . $request["query"] . "%");
                        } else {
                            $query = $query->orWhere($column, "like", "%" . $request["query"] . "%");
                        }
                    }
                    return $query;
                });
            } else {
                $query = $query->where($request["search_columns"], "like", "%" . $request["query"] . "%");
            }
        }

        // Order
        if (isset($request["order"])) {
            foreach ($request["order"] as $order) {
                $query = $query->orderBy($order["column"], $order["sort"] ?? "asc");
            }
        }

        // pagination
        if (isset($request["pagination"])) {
            if ($request["pagination"]["page_no"]) {
                return $query->paginate($request["pagination"]["per_page"] ?? 20, '*', 'page', $request["pagination"]["page_no"]);
            }
        }

        return $query->get();
    }
}
