<?php

namespace App\Helpers\Sql;

class MysqlQueryGenerator
{
    /**
     * @param string $databaseName
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
            $dataTypeString = self::getDataTypeString($column['DataType'], $column['Length']);

            $columnString = "`" . $column['Name'] . "`" . " " . $dataTypeString . " ";

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
     * @param bool $forModify
     * @return string
     */
    private static function getColumnStr($column, string $columnString, bool $forModify = false): string
    {
        if ($column['Nullable']) {
            $columnString .= "NULL ";
        } else {
            $columnString .= "NOT NULL ";
        }

        if ($column['AutoIncrement']) {
            $columnString .= "AUTO_INCREMENT ";
        }

        if ($column['PrimaryKey'] && !$forModify) {
            $columnString .= "PRIMARY KEY ";
        }

        if ($column['Unique'] && !$forModify) {
            $columnString .= "UNIQUE KEY ";
        }

        if (!is_null($column['DefaultValue'])) {
            $columnString .= "DEFAULT '{$column['DefaultValue']}' ";
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
        $dataTypeString = self::getDataTypeString($column['DataType'], $column['Length']);
        $columnString = "`{$column['Name']}` $dataTypeString ";

        $columnString = self::getColumnStr($column, $columnString);

        $sql .= trim($columnString) . ";";
        return $sql;
    }

    public static function getModifyColumnSql($databaseName, $tableName, $column): string
    {
        $sql = "ALTER TABLE `$databaseName`.`$tableName` MODIFY ";
        $dataTypeString = self::getDataTypeString($column['DataType'], $column['Length']);
        $columnString = "`{$column['Name']}` $dataTypeString ";

        $columnString = self::getColumnStr($column, $columnString, true);

        $sql .= trim($columnString) . ";";
        return $sql;
    }

    public static function getDeleteColumnSql($databaseName, $tableName, $columnName): string
    {
        return "ALTER TABLE `$databaseName`.`$tableName` DROP COLUMN `$columnName`;";
    }

    public static function getRenameColumnSql($databaseName, $tableName, $oldName, $newColumDefinition): string
    {
        $sql = "ALTER TABLE `$databaseName`.`$tableName` CHANGE `$oldName` ";
        $dataTypeString = self::getDataTypeString($newColumDefinition['DataType'], $newColumDefinition['Length']);
        $columnString = "`{$newColumDefinition['Name']}` $dataTypeString ";
        $columnString = self::getColumnStr($newColumDefinition, $columnString);
        $sql .= trim($columnString) . ";";
        return $sql;
    }

    public static function getAddPrimaryKeySql($databaseName, $tableName, $columnName): string
    {
        return "ALTER TABLE `$databaseName`.`$tableName` ADD PRIMARY KEY(`$columnName`);";
    }

    public static function getRemovePrimaryKeySql($databaseName, $tableName, $columnName): string
    {
        return "ALTER TABLE `$databaseName`.`$tableName` DROP INDEX `PRIMARY`;";
    }

    public static function getAddUniqueKeySql($databaseName, $tableName, $columnName): string
    {
        return "ALTER TABLE `$databaseName`.`$tableName` ADD UNIQUE(`$columnName`);";
    }

    public static function getRemoveUniqueKeySql($databaseName, $tableName, $columnName): string
    {
        return "ALTER TABLE `$databaseName`.`$tableName` DROP INDEX `$columnName`;";
    }
}
