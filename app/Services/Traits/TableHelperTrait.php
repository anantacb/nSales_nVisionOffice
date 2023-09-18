<?php

namespace App\Services\Traits;

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
}
