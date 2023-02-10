<?php

namespace App\Helpers\Sql;

class SqliteQueryGenerator
{
    /**
     * @param string $tableName
     * @param array $columnDefinitions
     * [
     * 'Name' => 'Id',
     * 'DataType' => 'int',
     * 'Length' => 11,
     * 'AutoIncrement' => true,
     * 'Nullable' => false,
     * 'DefaultValue' => null,
     * 'PrimaryKey' => true,
     * 'Unique' => false,
     * 'SortOrder' => 10
     * ]
     * @return string
     */
    public static function getCreateTableSql(
        string $tableName,
        array  $columnDefinitions): string
    {
        $sql = "DROP TABLE IF EXISTS `$tableName`;";
        $sql .= "CREATE TABLE IF NOT EXISTS `$tableName` (";

        $columnStrings = [];
        foreach ($columnDefinitions as $column) {
            $dataTypeString = self::getDataTypeString($column['DataType'], $column['Length']);

            $columnString = "`" . $column['Name'] . "`" . " " . $dataTypeString . " ";

            if ($column['AutoIncrement']) {
                $columnString .= "AUTO_INCREMENT ";
            }

            if ($column['PrimaryKey']) {
                $columnString .= "PRIMARY KEY ";
            } else {
                if ($column['Nullable']) {
                    $columnString .= "NULL ";
                } else {
                    $columnString .= "NOT NULL ";
                }
            }

            if (!is_null($column['DefaultValue'])) {
                $columnString .= "DEFAULT " . $column['DefaultValue'] . " ";
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
