<?php

namespace App\Services\Traits;

use App\Helpers\DbHelpers;

trait TableHelperTrait
{
    /**
     * @param $companyTableDatabases
     * @param $tableModuleCompanyDatabases
     * @param $table
     * @param array $specificDatabases
     * @return array|string[]
     */
    public function getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, array $specificDatabases = []): array
    {
        if ($specificDatabases) {
            return $specificDatabases;
        }

        $selectedDatabases = [];
        // Company Specific Table
        if ($companyTableDatabases) {
            $selectedDatabases = $companyTableDatabases;
        } else {
            switch ($table->Database) {
                case 'Company':
                    $selectedDatabases = array_merge($tableModuleCompanyDatabases, ['NVISION_TEMPLATE']);
                    break;
                case 'Office':
                    $selectedDatabases = ['NVISION_OFFICE'];
                    break;
                case 'Both':
                    $selectedDatabases = array_merge($tableModuleCompanyDatabases, ['NVISION_TEMPLATE', 'NVISION_OFFICE']);
                    break;
            }
        }
        return $selectedDatabases;
    }

    public function getCandidateDatabasesWithConnections(
        $companyTableDatabasesWithConnections,
        $tableModuleCompanyDatabasesWithConnections,
        $table,
        array $specificDatabasesWithConnection = []
    ): array
    {
        if ($specificDatabasesWithConnection) {
            return $specificDatabasesWithConnection;
        }

        $officeDatabaseWithConnection = DbHelpers::getOfficeDatabaseConnectionDetails();
        $templateDatabaseWithConnection = DbHelpers::getTemplateDatabaseConnectionDetails();

        $selectedDatabasesWithConnections = [];
        // Company Specific Table
        if ($companyTableDatabasesWithConnections) {
            $selectedDatabasesWithConnections = $companyTableDatabasesWithConnections;
        } else {
            switch ($table->Database) {
                case 'Company':
                    $selectedDatabasesWithConnections = array_merge($tableModuleCompanyDatabasesWithConnections, [$templateDatabaseWithConnection]);
                    break;
                case 'Office':
                    $selectedDatabasesWithConnections = [$officeDatabaseWithConnection];
                    break;
                case 'Both':
                    $selectedDatabasesWithConnections = array_merge($tableModuleCompanyDatabasesWithConnections, [$templateDatabaseWithConnection, $officeDatabaseWithConnection]);
                    break;
            }
        }
        return $selectedDatabasesWithConnections;
    }

}
