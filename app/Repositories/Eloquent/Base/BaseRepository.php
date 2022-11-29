<?php

namespace App\Repositories\Eloquent\Base;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * @param array $matchingAttributes
     * @param array $updateAttributes
     * @return Model
     */
    public function updateOrCreate(array $matchingAttributes, array $updateAttributes): Model
    {
        return $this->model->updateOrCreate($matchingAttributes, $updateAttributes);
    }

    /**
     * Returns paginated data.
     * Request Validated by PaginatedDataRequest
     * $request[
     *    "search_columns" => string | array,
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
        if (isset($request["filter_by_relation"]) && count($request["filter_by_relation"]) > 0) {
            foreach ($request["filter_by_relation"] as $filter) {
                $query = $query->whereHas($filter["relation"], function ($query) use ($filter) {
                    if ($filter["values"]) {
                        if (is_array($filter["values"])) {
                            if ($filter["operator"] === "!=") {
                                return $query->whereNotIn($filter["column"], $filter["values"]);
                            } else {
                                return $query->whereIn($filter["column"], $filter["values"]);
                            }
                        } else {
                            return $query->where($filter["column"], $filter["operator"], $filter["values"]);
                        }
                    }
                });
            }
        }

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

    /**
     * @param $id
     * @return Model|null
     */
    public function findById($id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * @param $key
     * @param $value
     * @return
     */
    public function findByKeyValue($key, $value)
    {
        return $this->model->where($key, $value)->first();
    }

    /**
     * @param $id
     * @param array $data
     * @return Model
     */
    public function findByIdAndUpdate($id, array $data)
    {
        $model = $this->model->find($id);
        $model->update($data);
        return $model;
    }

    /**
     * @param Model $model
     * @param array $data
     * @return Model $model
     */
    public function update(Model $model, array $data)
    {
        $model->update($data);
        return $model;
    }

    /**
     * @param $id
     * @return boolean
     */
    public function findByIdAndDelete($id)
    {
        return $this->model->find($id)->delete();
    }

    /**
     * @param Model $model
     * @return bool|null
     * @throws Exception
     */
    public function delete(Model $model)
    {
        return $model->delete();
    }

    /*
     *  get total count with conditions
     *  conditions in array, with key, operator and value
          [
             [
                'key', 'operator', 'value
             ]
          ]
     */

    public function totalCount(array $where = [])
    {
        return $this->model->where($where)->count();
    }

    /**
     * @param string $attribute ColumnName
     * @param string $operator = >= <=
     * @param $value
     * @return Collection
     */
    public function getByAttribute(string $attribute, $operator, $value)
    {
        return $this->model->where($attribute, $operator, $value)->get();
    }


    /**
     * @param array $attributes [['column' => '','operand' => '','value' => ''|array]]
     * @param array $data
     * @return boolean
     */
    public function getByAttributesAndUpdate(array $attributes, array $data)
    {
        $model = $this->getModel();
        foreach ($attributes as $attribute) {
            if (is_array($attribute['value'])) {
                if ($attribute['operand'] == "!=") {
                    $model = $model->whereNotIn($attribute['column'], $attribute['value']);
                } else {
                    $model = $model->whereIn($attribute['column'], $attribute['value']);
                }
            } else {
                $model = $model->where($attribute['column'], $attribute['operand'], $attribute['value']);
            }
        }
        return $model->update($data);
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param array $attributes [['column' => '','operand' => '','value' => ''|array]]
     * @return boolean
     * @throws Exception
     */
    public function deleteByAttributes(array $attributes)
    {
        $model = $this->getModel();
        foreach ($attributes as $attribute) {
            if (is_array($attribute['value'])) {
                if ($attribute['operand'] == "!=") {
                    $model = $model->whereNotIn($attribute['column'], $attribute['value']);
                } else {
                    $model = $model->whereIn($attribute['column'], $attribute['value']);
                }
            } else {
                $model = $model->where($attribute['column'], $attribute['operand'], $attribute['value']);
            }
        }
        return $model->delete();
    }

    /**
     * @param $id
     * @param array|string $relations
     */
    public function findByIdWithRelations($id, $relations)
    {
        return $this->model->with($relations)->find($id);
    }

    /**
     * @param array|string $relation
     */
    public function getWithRelations(array|string $relations)
    {
        return $this->model->with($relations)->get();
    }

    /**
     * @param Model $model
     * @param $relations array|string
     * @return Model
     */
    public function loadRelation(Model $model, $relations)
    {
        return $model->load($relations);
    }

    /**
     * @param Model $model
     * @param $relation
     * @return mixed
     */
    public function getRelationData(Model $model, $relation)
    {
        return $model->$relation;
    }

    public function getRelationMethodData(Model $model, $relation, $condition_value = '')
    {
        if ($condition_value) {
            return $model->$relation($condition_value);
        }
        return $model->$relation();
    }

    /**
     * @param Model $model
     * @param $relation
     * @param array $data
     * @return Model
     */
    public function relationCreate(Model $model, $relation, array $data)
    {
        return $model->$relation()->create($data);
    }

    /**
     * @param array $data
     * @return Model
     */
    public function create(array $data)
    {
        $model = $this->model->create($data);
        return $model->fresh();
    }

    /**
     * @param Model $model
     * @param $relation
     * @param $relational_model_ids array
     */
    public function sync(Model $model, $relation, array $relational_model_ids)
    {
        $model->$relation()->sync($relational_model_ids);
    }

    /**
     * @param Model $model
     * @param $relation
     * @param array|string $relational_model_value
     */
    public function syncWithoutDetaching(Model $model, $relation, $relational_model_value)
    {
        $model->$relation()->syncWithoutDetaching($relational_model_value);
    }

    /**
     * @param Model $model
     * @param string $relation
     */
    public function deleteRelatedModels(Model $model, string $relation)
    {
        $model->$relation()->delete();
    }

    /**
     * @param Model $model
     * @param string $relation
     * @param string|array $condition_value
     */
    public function detach(Model $model, string $relation, $condition_value = '')
    {
        if ($condition_value) {
            $model->$relation()->detach($condition_value);
        } else {
            $model->$relation()->detach();
        }
    }

    /**
     * @param array $data
     */
    public function insert(array $data)
    {
        $this->model->insert($data);
    }

    /**
     * @param string $column
     * @param array $attributes [['column' => '','operand' => '','value' => ''|array]]
     * @param string $order_by
     * @return mixed
     */
    public function distinct(string $column, array $attributes = [], string $order_by = '')
    {
        $model = $this->getModel();
        foreach ($attributes as $attribute) {
            if (is_array($attribute['value'])) {
                if ($attribute['operand'] == "!=") {
                    $model = $model->whereNotIn($attribute['column'], $attribute['value']);
                } else {
                    $model = $model->whereIn($attribute['column'], $attribute['value']);
                }
            } else {
                $model = $model->where($attribute['column'], $attribute['operand'], $attribute['value']);
            }
        }
        if ($order_by) {
            $model = $model->orderBy($order_by);
        }
        return $model->distinct()->pluck($column);
    }

    /**
     * @param string $column
     * @param array $attributes [['column' => '','operand' => '','value' => ''|array]]
     * @return mixed
     */
    public function max(string $column, array $attributes = [])
    {
        $model = $this->getModel();
        foreach ($attributes as $attribute) {
            if (is_array($attribute['value'])) {
                if ($attribute['operand'] == "!=") {
                    $model = $model->whereNotIn($attribute['column'], $attribute['value']);
                } else {
                    $model = $model->whereIn($attribute['column'], $attribute['value']);
                }
            } else {
                $model = $model->where($attribute['column'], $attribute['operand'], $attribute['value']);
            }
        }
        return $model->max($column);
    }

    /**
     * @param string $column
     * @param array $attributes [['column' => '','operand' => '','value' => ''|array]]
     * @return mixed
     */
    public function min(string $column, array $attributes = [])
    {
        $model = $this->getModel();
        foreach ($attributes as $attribute) {
            if (is_array($attribute['value'])) {
                if ($attribute['operand'] == "!=") {
                    $model = $model->whereNotIn($attribute['column'], $attribute['value']);
                } else {
                    $model = $model->whereIn($attribute['column'], $attribute['value']);
                }
            } else {
                $model = $model->where($attribute['column'], $attribute['operand'], $attribute['value']);
            }
        }
        return $model->min($column);
    }

    /**
     * Get resources by selected columns
     * @param array $columns ['one', 'two']
     * @return Collection
     */
    public function getBySelectedColumns(array $columns): Collection
    {
        if (count($columns)) {
            return $this->model->select($columns)->get();
        }

        return $this->all();
    }

    /**
     *
     * @return Collection
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * Find resource by criteria or relation
     * @param array $criteria
     * @param array $relation
     * @return Model
     */
    public function findOneBy(array $criteria = [], array $relation = [])
    {
        return $this->model->where($criteria)->with($relation)->first();
    }

    /**
     * @param array $attributes [['column' => '','operand' => '','value' => ''|array]]
     * @param array|string $relations
     * @param array|string $selectColumns
     * @param string $order_by
     * @param boolean $order_desc
     * @return Model
     */
    public function firstByAttributes(
        array $attributes,
              $relations = [],
              $selectColumns = '',
              $order_by = '',
              $order_desc = false
    )
    {
        $model = $this->getModel();

        if ($relations) {
            $model = $model->with($relations);
        }

        if ($selectColumns) {
            $model = $model->select($selectColumns);
        }
        foreach ($attributes as $attribute) {
            if (is_array($attribute['value'])) {
                if ($attribute['operand'] == "!=") {
                    $model = $model->whereNotIn($attribute['column'], $attribute['value']);
                } else {
                    $model = $model->whereIn($attribute['column'], $attribute['value']);
                }
            } else {
                $model = $model->where($attribute['column'], $attribute['operand'], $attribute['value']);
            }
        }

        if ($order_by) {
            if ($order_desc) {
                $model = $model->orderByDesc($order_by);
            } else {
                $model = $model->orderBy($order_by);
            }
        }

        return $model->first();
    }

    /**
     * @param array $attributes [['column' => '','operand' => '','value' => ''|array]]
     * @param array|string $relations
     * @param array|string $selectColumns
     * @param string $order_by
     * @param boolean $order_desc
     * @return Collection
     */
    public function getByAttributes(
        array $attributes,
              $relations = [],
              $selectColumns = '',
              $order_by = '',
              $order_desc = false
    )
    {
        $model = $this->getModel();

        if ($relations) {
            $model = $model->with($relations);
        }

        if ($selectColumns) {
            $model = $model->select($selectColumns);
        }

        foreach ($attributes as $attribute) {
            if (is_array($attribute['value'])) {
                if ($attribute['operand'] == "!=") {
                    $model = $model->whereNotIn($attribute['column'], $attribute['value']);
                } else {
                    $model = $model->whereIn($attribute['column'], $attribute['value']);
                }
            } else {
                $model = $model->where($attribute['column'], $attribute['operand'], $attribute['value']);
            }
        }

        if ($order_by) {
            if ($order_desc) {
                $model = $model->orderByDesc($order_by);
            } else {
                $model = $model->orderBy($order_by);
            }
        }
        return $model->get();
    }

    /**
     * @param Model $model
     * @param string $custom_column
     */
    public function append(Model $model, string $custom_column)
    {
        $model->append($custom_column);
    }

    /**
     * @param Model $model
     * @param string $relation
     * @param $relation_id
     * @param array $additional_data
     */
    public function attach(Model $model, string $relation, $relation_id, array $additional_data = [])
    {
        $model->$relation()->attach($relation_id, $additional_data);
    }

    public function truncate()
    {
        $this->getModel()->truncate();
    }
}
