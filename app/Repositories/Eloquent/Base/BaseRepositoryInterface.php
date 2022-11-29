<?php

namespace App\Repositories\Eloquent\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface BaseRepositoryInterface
{
    public function getModel();

    /**
     *
     * @return Collection
     */
    public function all();

    /**
     * Returns paginated data.
     * Request Validated by PaginatedDataRequest
     * $request[
     *    "selected_columns" => string | array,
     *    "relations" => [["name" => string, columns => null | array]],
     *    "filters" => [["column"=>"column_name", "operator" => "=,!=,...", values => string | array]],
     *    "filter_by_relation" => [["relation"=>string, "column"=>"column_name", "operator" => "=,!=,...", values => string | array]],
     *    "search" => ["columns" => string | array, query=> string]
     *    "order" => [["column"=>"column_name", "sort" => "asc|desc"]]
     *    "pagination" => [page_no=>int, per_page=>int]
     * ]
     */
    public function paginatedData(array $request);

    /**
     * @param $id
     * @return Model|null
     */
    public function findById($id): ?Model;

    /**
     * @param $key
     * @param $value
     */
    public function findByKeyValue($key, $value);

    /**
     * Get resources by selected columns
     * @param array $columns ['one', 'two']
     * @return Collection
     */
    public function getBySelectedColumns(array $columns): Collection;

    /**
     * @param string $attribute ColumnName
     * @param string $operator = >= <=
     * @param $value
     * @return Collection
     */
    public function getByAttribute(string $attribute, $operator, $value);

    /**
     * Find resource by criteria or relation
     * @param array $criteria
     * @param array $relation
     * @return Model
     */
    public function findOneBy(array $criteria = [], array $relation = []);

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
    );

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
    );

    /**
     * @param array $data
     * @return Model
     */
    public function create(array $data);

    /**
     * @param array $data
     */
    public function insert(array $data);

    /**
     * @param Model $model
     * @param array $data
     * @return Model $model
     */
    public function update(Model $model, array $data);

    /**
     * @param array $matchingAttributes
     * @param array $updateAttributes
     * @return mixed
     */
    public function updateOrCreate(array $matchingAttributes, array $updateAttributes);

    /**
     * @param $id
     * @param array $data
     * @return Model
     */
    public function findByIdAndUpdate($id, array $data);

    /**
     * @param array $attributes [['column' => '','operand' => '','value' => ''|array]]
     * @param array $data
     * @return boolean
     */
    public function getByAttributesAndUpdate(array $attributes, array $data);

    /**
     * @param $id
     * @return boolean
     */
    public function findByIdAndDelete($id);

    /**
     * @param array $attributes [['column' => '','operand' => '','value' => ''|array]]
     * @return boolean
     */
    public function deleteByAttributes(array $attributes);

    public function truncate();

    /**
     * @param $id
     * @param array|string $relations
     */
    public function findByIdWithRelations($id, $relations);

    /**
     * @param Model $model
     * @param $relations array|string
     * @return Model
     */
    public function loadRelation(Model $model, $relations);

    /**
     * @param array|string $relations
     * @return Collection
     */
    public function getWithRelations(array|string $relations);

    /**
     * @param Model $model
     * @param $relation
     * @return mixed
     */
    public function getRelationData(Model $model, $relation);

    public function getRelationMethodData(Model $model, $relation, $condition_value = '');

    /**
     * @param Model $model
     * @param $relation
     * @param array $data
     * @return Model
     */
    public function relationCreate(Model $model, $relation, array $data);

    /**
     * @param Model $model
     * @param $relation
     * @param $relational_model_ids array
     */
    public function sync(Model $model, $relation, array $relational_model_ids);

    /**
     * @param Model $model
     * @param $relation
     * @param array|string $relational_model_value
     */
    public function syncWithoutDetaching(Model $model, $relation, $relational_model_value);

    /**
     * @param Model $model
     * @param string $relation
     */
    public function deleteRelatedModels(Model $model, string $relation);

    /**
     * @param Model $model
     * @param string $custom_column
     */
    public function append(Model $model, string $custom_column);

    /**
     * @param Model $model
     * @param string $relation
     * @param $relation_id
     * @param array $additional_data
     */
    public function attach(Model $model, string $relation, $relation_id, array $additional_data = []);

    /**
     * @param Model $model
     * @param string $relation
     * @param string|array $condition_value
     */
    public function detach(Model $model, string $relation, $condition_value = '');

    /**
     *  get total count with conditions
     *  conditions in array, with key, operator and value
     * [
     * [
     * 'key', 'operator', 'value
     * ]
     * ]
     */
    public function totalCount(array $where = []);

    /**
     * @param string $column
     * @param array $attributes [['column' => '','operand' => '','value' => ''|array]]
     * @param string $order_by
     * @return mixed
     */
    public function distinct(string $column, array $attributes = [], string $order_by = '');

    /**
     * @param string $column
     * @param array $attributes [['column' => '','operand' => '','value' => ''|array]]
     * @return mixed
     */
    public function max(string $column, array $attributes = []);

    /**
     * @param string $column
     * @param array $attributes [['column' => '','operand' => '','value' => ''|array]]
     * @return mixed
     */
    public function min(string $column, array $attributes = []);
}
