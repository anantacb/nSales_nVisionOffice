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
        $this->mapEnumToString();

        $table = $this->getTable();

        $type = DB::connection($this->getConnectionName())
            ->select(DB::raw("SHOW COLUMNS FROM $table WHERE Field = '{$column}'"))[0]->Type;

        preg_match('/^enum\((.*)\)$/', $type, $matches);

        $enum_values = [];

        if (!$matches) {
            return $enum_values;
        }

        foreach (explode(',', $matches[1]) as $value) {
            $v = trim($value, "'");
            array_push($enum_values, $v);
        }
        return $enum_values;
    }

    private function mapEnumToString(): void
    {
        DB::connection($this->getConnectionName())
            ->getDoctrineSchemaManager()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping('enum', 'string');
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
        $this->mapEnumToString();

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

    public function getTableColumnsWithType(): array
    {
        $this->mapEnumToString();

        $columns = $this->getTableColumns();

        $column_with_types = [];
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
}
