<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait ModelHelper
{
    /**
     * @param $column
     * @return array
     */
    public function getEnumColumnValues($column): array
    {
        $enum_values = [];
        $table = $this->getTable();

        $type = DB::connection($this->getConnectionName())
            ->select("SHOW COLUMNS FROM $table WHERE Field = '{$column}'")[0]->Type;

        preg_match('/^enum\((.*)\)$/', $type, $matches);

        if (isset($matches[1])) {
            $enum_values = array_map(function ($value) {
                return trim($value, "'");
            }, explode(",", $matches[1]));
        }
        return $enum_values;
    }

    /**
     * @return array
     */
    public function getTableColumnsWithType(): array
    {
        $column_with_types = [];
        $columns = $this->getTableColumns();

        foreach ($columns as $column) {
            $type = $this
                ->getConnection()
                ->getSchemaBuilder()
                ->getColumnType($this->getTable(), $column);
            $column_with_types[] = [
                'name' => $column,
                'type' => $type
            ];
        }

        return $column_with_types;
    }

    /**
     * @return array
     */
    public function getTableColumns(): array
    {
        return $this
            ->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing($this->getTable());
    }

    /**
     * @param string $column_name
     * @return bool
     */
    public function isColumnExists(string $column_name): bool
    {
        return Schema::connection($this->getConnectionName())->hasColumn($this->getTable(), $column_name);
    }

    /**
     * @param string $type
     * @return array
     */
    public function getColumnsByType(string $type): array
    {
        $columns = $this->getTableColumns();
        $columns_by_types = [];

        foreach ($columns as $column) {
            $column_type = $this
                ->getConnection()
                ->getSchemaBuilder()
                ->getColumnType($this->getTable(), $column);
            if ($column_type === $type) {
                $columns_by_types[] = $column;
            }
        }
        return $columns_by_types;
    }

}
