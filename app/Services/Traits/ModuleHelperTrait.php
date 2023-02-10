<?php

namespace App\Services\Traits;

use App\Helpers\Sql\MysqlQueryGenerator;

trait ModuleHelperTrait
{
    private function getModulesCreateTableSqlQueries($module, $company): array
    {
        $companyId = $company->Id;
        $sqlQueries = [];
        $tablesToCreate = [];
        $module->tables->each(function ($table) use ($companyId, &$tablesToCreate) {
            if ($table->companyTables->count()) {
                // Is company Specific table
                $companyTableIds = $table->companyTables->pluck('TableId')->toArray();
                if (in_array($companyId, $companyTableIds)) {
                    $tablesToCreate[] = $table;
                }
            } else {
                $tablesToCreate[] = $table;
            }
        });

        foreach ($tablesToCreate as $tableToCreate) {
            $tableFields = [];
            $tableToCreate->tableFields->sortBy('SortOrder')->each(function ($tableField) use ($companyId, &$tableFields) {
                if ($tableField->companyTableFields->count()) {
                    $companyTableFieldCompanyIds = $tableField->companyTableFields->pluck('CompanyId')->toArray();
                    if (in_array($companyId, $companyTableFieldCompanyIds)) {

                        $tableFields[] = $tableField->only(['Name', 'DataType', 'Length', 'AutoIncrement', 'Nullable', 'DefaultValue', 'PrimaryKey', 'Unique', 'SortOrder']);
                    }
                } else {
                    $tableFields[] = $tableField->only(['Name', 'DataType', 'Length', 'AutoIncrement', 'Nullable', 'DefaultValue', 'PrimaryKey', 'Unique', 'SortOrder']);
                }
            });

            $sqlQueries[] = MysqlQueryGenerator::getCreateTableSql($company->DatabaseName, $tableToCreate->Name, $tableFields);
        }
        return $sqlQueries;
    }

    private function makeEntryInCompanyModuleTable($companyId, $moduleId): void
    {
        $this->companyModuleRepository->firstOrCreate([
            'ModuleId' => $moduleId,
            'CompanyId' => $companyId
        ]);
    }

    private function deleteEntryFromCompanyModuleTable($companyId, $moduleId): void
    {
        $this->companyModuleRepository->deleteByAttributes([
            ['column' => 'ModuleId', 'operand' => '=', 'value' => $moduleId],
            ['column' => 'CompanyId', 'operand' => '=', 'value' => $companyId]
        ]);
    }

    private function getModulesDeleteTableSqlQueries($module, $company): array
    {
        $companyId = $company->Id;
        $sqlQueries = [];
        $tablesToCreate = [];
        $module->tables->each(function ($table) use ($companyId, &$tablesToCreate) {
            if ($table->companyTables->count()) {
                // Is company Specific table
                $companyTableIds = $table->companyTables->pluck('TableId')->toArray();
                if (in_array($companyId, $companyTableIds)) {
                    $tablesToCreate[] = $table;
                }
            } else {
                $tablesToCreate[] = $table;
            }
        });

        foreach ($tablesToCreate as $tableToCreate) {
            $sqlQueries[] = MysqlQueryGenerator::getDropTableSql($company->DatabaseName, $tableToCreate->Name);
        }
        return $sqlQueries;
    }
}
