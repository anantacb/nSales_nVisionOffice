<?php

namespace App\Helpers\Sql;

class SqliteQueryGenerator
{
    /**
     * @param string $tableName
     * @param array $columnDefinitions
     * [
     *      'column' => 'Id',
     *      'type' => 'int',
     *      'size' => 11,
     *      'auto_increment' => true,
     *      'nullable' => false,
     *      'default' => null,
     *      'primary_key' => true
     * ],
     * @return string
     */
    public static function getCreateTableSql(
        string $tableName,
        array  $columnDefinitions): string
    {
        $sql = "DROP TABLE IF EXISTS `$tableName`;
                CREATE TABLE IF NOT EXISTS `$tableName` (";

        $columnStrings = [];
        foreach ($columnDefinitions as $column) {
            $dataTypeString = self::getDataTypeString($column['type'], $column['size']);

            $columnString = "`" . $column['column'] . "`" . " " . $dataTypeString . " ";

            if ($column['auto_increment']) {
                $columnString .= "AUTO_INCREMENT ";
            }

            if ($column['primary_key']) {
                $columnString .= "PRIMARY KEY ";
            } else {
                if ($column['nullable']) {
                    $columnString .= "null ";
                } else {
                    $columnString .= "not null ";
                }
            }

            if (!is_null($column['default'])) {
                $columnString .= "default " . $column['default'] . " ";
            }

            $columnStrings[] = trim($columnString);
        }

        return $sql . implode(",", $columnStrings) . ");";
    }

    private static function getDataTypeString($type, $size = null)
    {
        $dataTypeString = "";
        switch ($type) {
            case 'int':
                $dataTypeString = "int(" . $size . ")";
                break;
            case 'varchar':
                $dataTypeString = "varchar(" . $size . ")";
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
            case (bool)preg_match('/enum\(.*\)/', $type):
                $dataTypeString = $type;
                break;
            default:
                break;
        }
        return $dataTypeString;
    }
}
