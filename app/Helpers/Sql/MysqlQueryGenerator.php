<?php

namespace App\Helpers\Sql;

class MysqlQueryGenerator
{
    /**
     * @param string $databaseName
     * @param string $tableName
     * @param array $columnDefinitions
     * [
     * 'name' => 'Id',
     * 'data_type' => 'int',
     * 'length' => 11,
     * 'auto_increment' => true,
     * 'nullable' => false,
     * 'default' => null,
     * 'primary_key' => true,
     * 'unique_key' => false,
     * 'sort_order' => 10
     * ]
     * @param string $engine
     * @param string $charset
     * @param string $collate
     * @return string
     */
    public static function getCreateTableSql(
        string $databaseName,
        string $tableName,
        array  $columnDefinitions,
        string $engine = 'InnoDB',
        string $charset = 'utf8mb4',
        string $collate = 'utf8mb4_general_ci'): string
    {
        $sql = "CREATE TABLE IF NOT EXISTS ";

        if ($databaseName) {
            $sql .= "`$databaseName`.";
        }

        $sql .= "`$tableName` ( ";

        $columnStrings = [];
        foreach ($columnDefinitions as $column) {
            $dataTypeString = self::getDataTypeString($column['data_type'], $column['length']);

            $columnString = "`" . $column['name'] . "`" . " " . $dataTypeString . " ";

            $columnString = self::getColumnStr($column, $columnString);

            $columnStrings[] = trim($columnString);
        }

        return $sql . implode(",", $columnStrings) . ") ENGINE=" . $engine . " DEFAULT CHARSET=" . $charset . " COLLATE=" . $collate . ";";
    }

    private static function getDataTypeString($data_type, $length = null)
    {
        $dataTypeString = "";
        switch ($data_type) {
            case 'int':
                $dataTypeString = "int(" . $length . ")";
                break;
            case 'varchar':
                $dataTypeString = "varchar(" . $length . ")";
                break;
            case 'text':
                $dataTypeString = "text";
                break;
            case 'datetime':
                $dataTypeString = "datetime";
                break;
            case 'double':
                $dataTypeString = "double";
                break;
            case 'longtext':
                $dataTypeString = "longtext";
                break;
            case 'tinytext':
                $dataTypeString = "tinytext";
                break;
            case 'tinyint':
                $dataTypeString = "tinyint";
                break;
            case 'blob':
                $dataTypeString = "blob";
                break;
            case 'longblob':
                $dataTypeString = "longblob";
                break;
            case (bool)preg_match('/enum\(.*\)/', $data_type):
                $dataTypeString = $data_type;
                break;
            default:
                break;
        }
        return $dataTypeString;
    }

    /**
     * @param $column
     * @param string $columnString
     * @return string
     */
    private static function getColumnStr($column, string $columnString): string
    {
        if ($column['nullable']) {
            $columnString .= "null ";
        } else {
            $columnString .= "not null ";
        }

        if ($column['auto_increment']) {
            $columnString .= "AUTO_INCREMENT ";
        }

        if ($column['primary_key']) {
            $columnString .= "PRIMARY KEY ";
        }

        if ($column['unique_key']) {
            $columnString .= "UNIQUE KEY ";
        }

        if (!is_null($column['default'])) {
            $columnString .= "default '{$column['default']}' ";
        }
        return $columnString;
    }

    /**
     * @param $databaseName
     * @param $tableName
     * @return string
     */
    public static function getDropTableSql($databaseName, $tableName): string
    {
        return "DROP TABLE IF EXISTS `$databaseName`.`$tableName`;";
    }

    public static function getAddColumnSql($databaseName, $tableName, $column): string
    {
        $sql = "ALTER TABLE `$databaseName`.`$tableName` ADD ";
        $dataTypeString = self::getDataTypeString($column['data_type'], $column['length']);
        $columnString = "`{$column['name']}` $dataTypeString ";

        $columnString = self::getColumnStr($column, $columnString);

        $sql .= trim($columnString) . ";";
        return $sql;
    }

    public static function getDeleteColumnSql($databaseName, $tableName, $columnName)
    {
        return "ALTER TABLE `$databaseName`.`$tableName` DROP COLUMN `$columnName`";
    }

    public static function getRenameColumnSql($databaseName, $tableName, $oldName, $newColumDefinition)
    {
        $sql = "ALTER TABLE `$databaseName`.`$tableName` CHANGE `$oldName` ";
        $dataTypeString = self::getDataTypeString($newColumDefinition['data_type'], $newColumDefinition['length']);
        $columnString = "`{$newColumDefinition['name']}` $dataTypeString ";
        $columnString = self::getColumnStr($newColumDefinition, $columnString);
        $sql .= trim($columnString) . ";";
        return $sql;
    }
}
