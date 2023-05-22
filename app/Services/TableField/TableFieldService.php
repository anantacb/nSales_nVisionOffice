<?php

namespace App\Services\TableField;


use App\Contracts\ServiceDto;
use App\Helpers\Sql\MysqlQueryGenerator;
use App\Helpers\SqlFormatter;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyTable\CompanyTableRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyTableField\CompanyTableFieldRepositoryInterface;
use App\Repositories\Eloquent\Office\Table\TableRepositoryInterface;
use App\Repositories\Eloquent\Office\TableField\TableFieldRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TableFieldService implements TableFieldServiceInterface
{
    protected TableRepositoryInterface $tableRepository;
    protected CompanyTableRepositoryInterface $companyTableRepository;
    protected TableFieldRepositoryInterface $tableFieldRepository;
    //protected ModuleRepositoryInterface $moduleRepository;
    protected CompanyRepositoryInterface $companyRepository;

    protected CompanyTableFieldRepositoryInterface $companyTableFieldRepository;

    public function __construct(
        TableRepositoryInterface             $tableRepository,
        TableFieldRepositoryInterface        $tableFieldRepository,
        CompanyRepositoryInterface           $companyRepository,
        CompanyTableRepositoryInterface      $companyTableRepository,
        CompanyTableFieldRepositoryInterface $companyTableFieldRepository
    )
    {
        $this->tableRepository = $tableRepository;
        $this->tableFieldRepository = $tableFieldRepository;
        $this->companyRepository = $companyRepository;
        $this->companyTableRepository = $companyTableRepository;
        $this->companyTableFieldRepository = $companyTableFieldRepository;
    }

    public function getTableFields(Request $request): ServiceDto
    {
        $tableId = $request->get('tableId');
        $relations = [
            'companyTableFields' => function ($q) {
                $q->with([
                    'company' => function ($q1) {
                        $q1->select(['Id', 'Name', 'CompanyName']);
                    }
                ])->select(['Id', 'CompanyId', 'TableFieldId']);
            }
        ];
        $tableFields = $this->tableFieldRepository->getByAttributes([
            ['column' => 'TableId', 'operand' => '=', 'value' => $tableId]
        ],
            $relations,
            [
                'Id',
                'TableId',
                'SortOrder',
                'Name',
                'DataType',
                'Type',
                'Length',
                'Nullable',
                'Unique',
                'PrimaryKey',
                'DefaultValue',
                'AutoIncrement',
                'Disabled'
            ],
            'SortOrder'
        );
        return new ServiceDto("TableFields Retrieved Successfully.", 200, $tableFields);
    }

    public function getTableFieldsOperationPreviews(Request $request): ServiceDto
    {
        $tableId = $request->get('tableId');
        $table = $this->tableRepository->firstByAttributes(
            [
                ['column' => 'Id', 'operand' => '=', 'value' => $tableId]
            ],
            ['companyTables.company', 'module.companies']
        );

        $data = [
            'sqlPreview' => false,
            'sqlPreviewMessage' => "",
            'sqlPreviews' => [],
            'sqlitePreview' => false,
            'sqlitePreviewMessage' => "",
            'sqlitePreviews' => []
        ];

        if (in_array($table->Type, ['Server', 'Both'])) {
            $tableModuleCompanyDatabases = $table->module->companies->pluck('DatabaseName')->toArray();
            $companyTableDatabases = $table->companyTables->pluck('company.DatabaseName')->toArray();

            $sqlQueries = [];

            // Add Section
            foreach ($request->get('newFields') as $newField) {
                if (in_array($newField['Type'], ['Server', 'Both'])) {
                    // This Field Is CompanySpecific
                    $tableFieldSpecificDatabases = $this->getDatabaseNamesByCompanyIds($newField['companies']);
                    $selectedDatabases = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableFieldSpecificDatabases);

                    foreach ($selectedDatabases as $database) {
                        $sql = MysqlQueryGenerator::getAddColumnSql($database, $table->Name, $newField);
                        $sqlQueries[] = $sql;
                    }
                }
            }

            // Delete Section
            $tableFieldsToDelete = $this->tableFieldRepository->getByAttributes(
                [
                    ['column' => 'Id', 'operand' => '=', 'value' => $request->get('tableFieldIdsToDelete')]
                ],
                ['companyTableFields.company']
            );

            foreach ($tableFieldsToDelete as $tableFieldToDelete) {
                if (in_array($tableFieldToDelete->Type, ['Server', 'Both'])) {
                    // Company Specific Table Field
                    $tableFieldSpecificDatabases = $tableFieldToDelete->companyTableFields->pluck('company.DatabaseName')->toArray();
                    $selectedDatabases = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableFieldSpecificDatabases);
                    foreach ($selectedDatabases as $database) {
                        $sql = MysqlQueryGenerator::getDeleteColumnSql($database, $table->Name, $tableFieldToDelete->Name);
                        $sqlQueries[] = $sql;
                    }
                }
            }

            // Update Section
            $requestedUpdatedTableFields = $request->get('updatedTableFields');
            $updatedTableFieldsRows = $this->tableFieldRepository->getByAttributes(
                [
                    ['column' => 'Id', 'operand' => '=', 'value' => collect($requestedUpdatedTableFields)->pluck('Id')->toArray()]
                ],
                ['companyTableFields.company']
            )->groupBy('Id');

            foreach ($requestedUpdatedTableFields as $requestedUpdatedTableField) {
                $existingTableFieldData = $updatedTableFieldsRows[$requestedUpdatedTableField['Id']][0];

                /*-------------- Rename Column Start -----------------*/
                $hasRenameOperation = in_array('Name', $requestedUpdatedTableField['updatedSections']);
                if ($hasRenameOperation) {
                    if (in_array($requestedUpdatedTableField['Type'], ['Server', 'Both'])) {
                        $tableFieldSpecificDatabases = $existingTableFieldData->companyTableFields->pluck('company.DatabaseName')->toArray();
                        $selectedDatabasesForUpdate = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableFieldSpecificDatabases);
                        foreach ($selectedDatabasesForUpdate as $database) {
                            $sql = MysqlQueryGenerator::getRenameColumnSql($database, $table->Name, $existingTableFieldData->Name, $requestedUpdatedTableField);
                            $sqlQueries[] = $sql;
                        }
                    }
                }
                /*-------------- Rename Column End -----------------*/

                /**
                 * Skip Other Column Operations If Rename Operation is present
                 */

                if (
                    !$hasRenameOperation &&
                    (
                        in_array('DataType', $requestedUpdatedTableField['updatedSections']) ||
                        in_array('Length', $requestedUpdatedTableField['updatedSections']) ||
                        in_array('Nullable', $requestedUpdatedTableField['updatedSections']) ||
                        in_array('Default', $requestedUpdatedTableField['updatedSections']) ||
                        in_array('AutoIncrement', $requestedUpdatedTableField['updatedSections'])
                    )
                ) {
                    if (in_array($requestedUpdatedTableField['Type'], ['Server', 'Both'])) {
                        $tableFieldSpecificDatabases = $existingTableFieldData->companyTableFields->pluck('company.DatabaseName')->toArray();
                        $selectedDatabasesForColumnModification = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableFieldSpecificDatabases);
                        foreach ($selectedDatabasesForColumnModification as $database) {
                            $sql = MysqlQueryGenerator::getModifyColumnSql($database, $table->Name, $requestedUpdatedTableField);
                            $sqlQueries[] = $sql;
                        }
                    }
                }

                if (
                    !$hasRenameOperation &&
                    (
                        in_array('PrimaryKey', $requestedUpdatedTableField['updatedSections']) ||
                        in_array('Unique', $requestedUpdatedTableField['updatedSections'])
                    )
                ) {
                    if (in_array($requestedUpdatedTableField['Type'], ['Server', 'Both'])) {
                        $tableFieldSpecificDatabases = $existingTableFieldData->companyTableFields->pluck('company.DatabaseName')->toArray();
                        $selectedDatabasesForColumnModification = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableFieldSpecificDatabases);

                        if (in_array('PrimaryKey', $requestedUpdatedTableField['updatedSections'])) {
                            foreach ($selectedDatabasesForColumnModification as $database) {
                                if ($requestedUpdatedTableField['PrimaryKey']) {
                                    // Add Primary Key
                                    $sql = MysqlQueryGenerator::getAddPrimaryKeySql($database, $table->Name, $requestedUpdatedTableField['Name']);
                                } else {
                                    // Delete Primary Key
                                    $sql = MysqlQueryGenerator::getRemovePrimaryKeySql($database, $table->Name, $requestedUpdatedTableField['Name']);
                                }

                                $sqlQueries[] = $sql;
                            }
                        }

                        if (in_array('Unique', $requestedUpdatedTableField['updatedSections'])) {
                            foreach ($selectedDatabasesForColumnModification as $database) {
                                if ($requestedUpdatedTableField['Unique']) {
                                    // Add Unique Key
                                    $sql = MysqlQueryGenerator::getAddUniqueKeySql($database, $table->Name, $requestedUpdatedTableField['Name']);
                                } else {
                                    // Delete Unique Key
                                    $sql = MysqlQueryGenerator::getRemoveUniqueKeySql($database, $table->Name, $requestedUpdatedTableField['Name']);
                                }

                                $sqlQueries[] = $sql;
                            }
                        }
                    }
                }

                /*-------------- Add/Delete Field in/from Company Start -----------------*/
                // Add or delete columns with
                if (in_array('Company', $requestedUpdatedTableField['updatedSections'])) {
                    if (in_array($requestedUpdatedTableField['Type'], ['Server', 'Both'])) {
                        $existingTableFieldDatabases = $existingTableFieldData->companyTableFields->pluck('company.DatabaseName')->toArray();
                        $requestedDatabases = $this->companyRepository->getByAttributes([
                            ['column' => 'Id', 'operand' => '=', 'value' => $requestedUpdatedTableField['companies']]
                        ])->pluck('DatabaseName')->toArray();

                        $removedDatabases = array_diff($existingTableFieldDatabases, $requestedDatabases);
                        $newDatabases = array_diff($requestedDatabases, $existingTableFieldDatabases);

                        // Add Column to Databases
                        foreach ($newDatabases as $newDatabase) {
                            $sql = MysqlQueryGenerator::getAddColumnSql($newDatabase, $table->Name, $requestedUpdatedTableField);
                            $sqlQueries[] = $sql;
                        }

                        // Drop Column from Databases
                        foreach ($removedDatabases as $removedDatabase) {
                            $sql = MysqlQueryGenerator::getDeleteColumnSql($removedDatabase, $table->Name, $requestedUpdatedTableField['Name']);
                            $sqlQueries[] = $sql;
                        }
                    }
                }
                /*-------------- Add/Delete Field in/from Company End -----------------*/

                if (in_array('Type', $requestedUpdatedTableField['updatedSections'])) {
                    /**
                     * Was a client Type Field, Now became server or both then need to add in databases
                     * Was a server or both type Field, Now become Client then need to remove from databases
                     */
                    if (in_array($existingTableFieldData->Type, ['Server', 'Both']) && $requestedUpdatedTableField['Type'] == 'Client') {
                        // Remove column from Databases
                        $tableFieldSpecificDatabases = $this->getDatabaseNamesByCompanyIds($requestedUpdatedTableField['companies']);
                        $selectedDatabases = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableFieldSpecificDatabases);
                        foreach ($selectedDatabases as $database) {
                            $sql = MysqlQueryGenerator::getDeleteColumnSql($database, $table->Name, $requestedUpdatedTableField['Name']);
                            $sqlQueries[] = $sql;
                        }
                    }
                    if ($existingTableFieldData->Type == 'Client' && in_array($requestedUpdatedTableField['Type'], ['Server', 'Both'])) {
                        // Add Columns in databases
                        $tableFieldSpecificDatabases = $this->getDatabaseNamesByCompanyIds($requestedUpdatedTableField['companies']);
                        $selectedDatabases = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableFieldSpecificDatabases);
                        foreach ($selectedDatabases as $database) {
                            $sql = MysqlQueryGenerator::getAddColumnSql($database, $table->Name, $requestedUpdatedTableField);
                            $sqlQueries[] = $sql;
                        }
                    }
                }
            }

            $data['sqlPreview'] = true;
            $data['sqlPreviews'] = collect($sqlQueries)->map(function ($sqlQuery) {
                return SqlFormatter::format($sqlQuery);
            })->toArray();
        } elseif ($request->get('type') == 'Client') {
            $data['sqlitePreviews'] = [];
            $data['sqlitePreview'] = true;
            $data['sqlPreviewMessage'] = "No Sql generated as Type is `Client`";
        }

        return new ServiceDto("TableFields Retrieved Successfully.", 200, $data);
    }

    private function getDatabaseNamesByCompanyIds(array $companyIds): array
    {
        if ($companyIds) {
            return $this->companyRepository->getByAttributes([
                ['column' => 'Id', 'operand' => '=', 'value' => $companyIds]
            ])->pluck('DatabaseName')->toArray();
        }
        return [];
    }

    private function getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableFieldSpecificDatabases = []): array
    {
        if ($tableFieldSpecificDatabases) {
            return $tableFieldSpecificDatabases;
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

    public function tableFieldsOperationsSaveAndExecute(Request $request): ServiceDto
    {
        $tableId = $request->get('tableId');
        $table = $this->tableRepository->firstByAttributes(
            [
                ['column' => 'Id', 'operand' => '=', 'value' => $tableId]
            ],
            ['companyTables.company', 'module.companies']
        );

        $tableModuleCompanyIds = $table->module->companies->pluck('Id')->toArray();
        $tableModuleCompanyDatabases = $table->module->companies->pluck('DatabaseName')->toArray();

        $companyTableCompanyIds = $table->companyTables->pluck('company.Id')->toArray();
        $companyTableDatabases = $table->companyTables->pluck('company.DatabaseName')->toArray();

        $sqlQueries = [];

        // Add Section
        foreach ($request->get('newFields') as $newField) {
            // Entry In TableField Table
            $newTableField = $this->createTableField($newField);

            if ($newField['companies']) {
                // This Field Is CompanySpecific
                $selectedCompanies = $this->companyRepository->getByAttributes([
                    ['column' => 'Id', 'operand' => '=', 'value' => $newField['companies']]
                ]);
                $selectedCompanyIds = $selectedCompanies->pluck('Id')->toArray();
                // Entry
                foreach ($selectedCompanyIds as $selectedCompanyId) {
                    $this->companyTableFieldRepository->create([
                        'CompanyId' => $selectedCompanyId,
                        'TableFieldId' => $newTableField->Id
                    ]);
                }
                $selectedDatabases = $selectedCompanies->pluck('DatabaseName')->toArray();
            } else {
                $selectedDatabases = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table);
            }

            // Generate Sql Queries if Server or Both
            if (in_array($newField['Type'], ['Server', 'Both'])) {
                foreach ($selectedDatabases as $database) {
                    $sql = MysqlQueryGenerator::getAddColumnSql($database, $table->Name, $newField);
                    $sqlQueries[] = $sql;
                }
            }
        }

        // Delete Section
        $tableFieldsToDelete = $this->tableFieldRepository->getByAttributes(
            [
                ['column' => 'Id', 'operand' => '=', 'value' => $request->get('tableFieldIdsToDelete')]
            ],
            ['companyTableFields.company']
        );

        foreach ($tableFieldsToDelete as $tableFieldToDelete) {
            $tableFieldSpecificDatabases = $tableFieldToDelete->companyTableFields->pluck('company.DatabaseName')->toArray();
            $selectedDatabases = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableFieldSpecificDatabases);

            $this->tableFieldRepository->findByIdAndDelete($tableFieldToDelete->Id);
            $this->companyTableFieldRepository->deleteByAttributes([
                ['column' => 'TableFieldId', 'operand' => '=', 'value' => $tableFieldToDelete->Id]
            ]);

            // Generate Sql Queries if Server or Both
            if (in_array($tableFieldToDelete->Type, ['Server', 'Both'])) {
                foreach ($selectedDatabases as $database) {
                    $sql = MysqlQueryGenerator::getDeleteColumnSql($database, $table->Name, $tableFieldToDelete->Name);
                    $sqlQueries[] = $sql;
                }
            }
        }

        // Update Section
        $requestedUpdatedTableFields = $request->get('updatedTableFields');
        $updatedTableFieldsRows = $this->tableFieldRepository->getByAttributes(
            [
                ['column' => 'Id', 'operand' => '=', 'value' => collect($requestedUpdatedTableFields)->pluck('Id')->toArray()]
            ],
            ['companyTableFields.company']
        )->groupBy('Id');

        foreach ($requestedUpdatedTableFields as $requestedUpdatedTableField) {
            $existingTableFieldData = $updatedTableFieldsRows[$requestedUpdatedTableField['Id']][0];

            // Update TableField Entry
            $this->tableFieldRepository->findByIdAndUpdate($existingTableFieldData->Id, [
                'Name' => $requestedUpdatedTableField['Name'],
                'DataType' => $requestedUpdatedTableField['DataType'],
                'Type' => $requestedUpdatedTableField['Type'],
                'Length' => $requestedUpdatedTableField['Length'],
                'Nullable' => $requestedUpdatedTableField['Nullable'],
                'Unique' => $requestedUpdatedTableField['Unique'],
                'PrimaryKey' => $requestedUpdatedTableField['PrimaryKey'],
                'DefaultValue' => $requestedUpdatedTableField['DefaultValue'],
                'AutoIncrement' => $requestedUpdatedTableField['AutoIncrement'],
                'SortOrder' => $requestedUpdatedTableField['SortOrder'],
            ]);

            /*-------------- Rename Column Start -----------------*/
            $hasRenameOperation = in_array('Name', $requestedUpdatedTableField['updatedSections']);
            if ($hasRenameOperation) {
                if (in_array($requestedUpdatedTableField['Type'], ['Server', 'Both'])) {
                    $tableFieldSpecificDatabases = $existingTableFieldData->companyTableFields->pluck('company.DatabaseName')->toArray();
                    $selectedDatabasesForUpdate = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableFieldSpecificDatabases);
                    foreach ($selectedDatabasesForUpdate as $database) {
                        $sql = MysqlQueryGenerator::getRenameColumnSql($database, $table->Name, $existingTableFieldData->Name, $requestedUpdatedTableField);
                        $sqlQueries[] = $sql;
                    }
                }
            }
            /*-------------- Rename Column End -----------------*/

            /**
             * Skip Other Column Operations If Rename Operation is present
             */

            if (
                !$hasRenameOperation &&
                (
                    in_array('DataType', $requestedUpdatedTableField['updatedSections']) ||
                    in_array('Length', $requestedUpdatedTableField['updatedSections']) ||
                    in_array('Nullable', $requestedUpdatedTableField['updatedSections']) ||
                    in_array('Default', $requestedUpdatedTableField['updatedSections']) ||
                    in_array('AutoIncrement', $requestedUpdatedTableField['updatedSections'])
                )
            ) {
                if (in_array($requestedUpdatedTableField['Type'], ['Server', 'Both'])) {
                    $tableFieldSpecificDatabases = $existingTableFieldData->companyTableFields->pluck('company.DatabaseName')->toArray();
                    $selectedDatabasesForColumnModification = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableFieldSpecificDatabases);
                    foreach ($selectedDatabasesForColumnModification as $database) {
                        $sql = MysqlQueryGenerator::getModifyColumnSql($database, $table->Name, $requestedUpdatedTableField);
                        $sqlQueries[] = $sql;
                    }
                }
            }

            if (
                !$hasRenameOperation &&
                (
                    in_array('PrimaryKey', $requestedUpdatedTableField['updatedSections']) ||
                    in_array('Unique', $requestedUpdatedTableField['updatedSections'])
                )
            ) {
                if (in_array($requestedUpdatedTableField['Type'], ['Server', 'Both'])) {
                    $tableFieldSpecificDatabases = $existingTableFieldData->companyTableFields->pluck('company.DatabaseName')->toArray();
                    $selectedDatabasesForColumnModification = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableFieldSpecificDatabases);

                    if (in_array('PrimaryKey', $requestedUpdatedTableField['updatedSections'])) {
                        foreach ($selectedDatabasesForColumnModification as $database) {
                            if ($requestedUpdatedTableField['PrimaryKey']) {
                                // Add Primary Key
                                $sql = MysqlQueryGenerator::getAddPrimaryKeySql($database, $table->Name, $requestedUpdatedTableField['Name']);
                            } else {
                                // Delete Primary Key
                                $sql = MysqlQueryGenerator::getRemovePrimaryKeySql($database, $table->Name, $requestedUpdatedTableField['Name']);
                            }

                            $sqlQueries[] = $sql;
                        }
                    }

                    if (in_array('Unique', $requestedUpdatedTableField['updatedSections'])) {
                        foreach ($selectedDatabasesForColumnModification as $database) {
                            if ($requestedUpdatedTableField['Unique']) {
                                // Add Unique Key
                                $sql = MysqlQueryGenerator::getAddUniqueKeySql($database, $table->Name, $requestedUpdatedTableField['Name']);
                            } else {
                                // Delete Unique Key
                                $sql = MysqlQueryGenerator::getRemoveUniqueKeySql($database, $table->Name, $requestedUpdatedTableField['Name']);
                            }

                            $sqlQueries[] = $sql;
                        }
                    }
                }
            }

            /*-------------- Add/Delete Field in/from Company Start -----------------*/
            // Add or delete columns with
            if (in_array('Company', $requestedUpdatedTableField['updatedSections'])) {
                $existingTableFieldCompanyIds = $existingTableFieldData->companyTableFields->pluck('company.Id')->toArray();
                $requestedCompanies = $this->companyRepository->getByAttributes([
                    ['column' => 'Id', 'operand' => '=', 'value' => $requestedUpdatedTableField['companies']]
                ]);
                $requestedCompanyIds = $requestedCompanies->pluck('Id')->toArray();

                // Add CompanyTableField Entry
                $newCompanyIds = array_diff($requestedCompanyIds, $existingTableFieldCompanyIds);
                foreach ($newCompanyIds as $companyId) {
                    $this->companyTableFieldRepository->create([
                        'CompanyId' => $companyId,
                        'TableFieldId' => $existingTableFieldData->Id
                    ]);
                }

                // Remove CompanyTableField Entry
                $removedCompanyIds = array_diff($existingTableFieldCompanyIds, $requestedCompanyIds);
                $this->companyTableFieldRepository->deleteByAttributes([
                    ['column' => 'TableFieldId', 'operand' => '=', 'value' => $existingTableFieldData->Id],
                    ['column' => 'CompanyId', 'operand' => '=', 'value' => $removedCompanyIds],
                ]);


                if (in_array($requestedUpdatedTableField['Type'], ['Server', 'Both'])) {
                    $existingTableFieldDatabases = $existingTableFieldData->companyTableFields->pluck('company.DatabaseName')->toArray();
                    $requestedDatabases = $requestedCompanies->pluck('DatabaseName')->toArray();

                    // Add Column to Databases
                    $newDatabases = array_diff($requestedDatabases, $existingTableFieldDatabases);
                    foreach ($newDatabases as $newDatabase) {
                        $sql = MysqlQueryGenerator::getAddColumnSql($newDatabase, $table->Name, $requestedUpdatedTableField);
                        $sqlQueries[] = $sql;
                    }

                    // Drop Column from Databases
                    $removedDatabases = array_diff($existingTableFieldDatabases, $requestedDatabases);
                    foreach ($removedDatabases as $removedDatabase) {
                        $sql = MysqlQueryGenerator::getDeleteColumnSql($removedDatabase, $table->Name, $requestedUpdatedTableField['Name']);
                        $sqlQueries[] = $sql;
                    }
                }
            }
            /*-------------- Add/Delete Field in/from Company End -----------------*/

            if (in_array('Type', $requestedUpdatedTableField['updatedSections'])) {
                /**
                 * Was a client Type Field, Now became server or both then need to add in databases
                 * Was a server or both type Field, Now become Client then need to remove from databases
                 */
                if (in_array($existingTableFieldData->Type, ['Server', 'Both']) && $requestedUpdatedTableField['Type'] == 'Client') {
                    // Remove column from Databases
                    $tableFieldSpecificDatabases = $this->getDatabaseNamesByCompanyIds($requestedUpdatedTableField['companies']);
                    $selectedDatabases = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableFieldSpecificDatabases);
                    foreach ($selectedDatabases as $database) {
                        $sql = MysqlQueryGenerator::getDeleteColumnSql($database, $table->Name, $requestedUpdatedTableField['Name']);
                        $sqlQueries[] = $sql;
                    }
                }

                if ($existingTableFieldData->Type == 'Client' && in_array($requestedUpdatedTableField['Type'], ['Server', 'Both'])) {
                    // Add Columns in databases
                    $tableFieldSpecificDatabases = $this->getDatabaseNamesByCompanyIds($requestedUpdatedTableField['companies']);
                    $selectedDatabases = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableFieldSpecificDatabases);
                    foreach ($selectedDatabases as $database) {
                        $sql = MysqlQueryGenerator::getAddColumnSql($database, $table->Name, $requestedUpdatedTableField);
                        $sqlQueries[] = $sql;
                    }
                }
            }
        }


        $totalQueries = count($sqlQueries);
        $successful = 0;
        $failed = 0;

        // Execute All Queries
        if (in_array($table->Type, ['Server', 'Both'])) {
            foreach ($sqlQueries as $sql) {
                try {
                    DB::statement($sql);
                    $successful += 1;
                } catch (Exception $exception) {
                    /*if (App::environment('production')) {
                        return new ServiceDto($exception->getMessage(), 500);
                    }*/
                    $failed += 1;
                    Log::error("Query execution failed (tableFieldsOperationsSaveAndExecute).  Query: $sql");
                }
            }
        }

        // Update Table Version
        $this->tableRepository->findByIdAndUpdate($tableId, [
            'Version' => $table->Version + 1
        ]);

        return new ServiceDto(
            "Total: $totalQueries\nSuccess: $successful\nFailed: $failed\nTableFields Operation Save And Execute Finished Successfully.",
            200,
            []
        );
    }

    /**
     * @param mixed $newField
     * @return Model
     */
    private function createTableField(array $newField): Model
    {
        return $this->tableFieldRepository->create(
            [
                'TableId' => $newField['TableId'],
                'SortOrder' => $newField['SortOrder'],
                'Name' => $newField['Name'],
                'DataType' => $newField['DataType'],
                'Type' => $newField['Type'],
                'Length' => $newField['Length'],
                'Nullable' => $newField['Nullable'],
                'Unique' => $newField['Unique'],
                'PrimaryKey' => $newField['PrimaryKey'],
                'DefaultValue' => $newField['DefaultValue'],
                'AutoIncrement' => $newField['AutoIncrement']
            ]
        );
    }

    public function tableFieldsOperationsSaveWithoutExecuting(Request $request): ServiceDto
    {
        $tableId = $request->get('tableId');
        $table = $this->tableRepository->firstByAttributes(
            [
                ['column' => 'Id', 'operand' => '=', 'value' => $tableId]
            ],
            ['companyTables.company', 'module.companies']
        );

        $tableModuleCompanyDatabases = $table->module->companies->pluck('DatabaseName')->toArray();
        $companyTableDatabases = $table->companyTables->pluck('company.DatabaseName')->toArray();

        // Add Section
        foreach ($request->get('newFields') as $newField) {
            // Entry In TableField Table
            $newTableField = $this->createTableField($newField);

            if ($newField['companies']) {
                // This Field Is CompanySpecific
                $selectedCompanies = $this->companyRepository->getByAttributes([
                    ['column' => 'Id', 'operand' => '=', 'value' => $newField['companies']]
                ]);
                $selectedCompanyIds = $selectedCompanies->pluck('Id')->toArray();
                // Entry
                foreach ($selectedCompanyIds as $selectedCompanyId) {
                    $this->companyTableFieldRepository->create([
                        'CompanyId' => $selectedCompanyId,
                        'TableFieldId' => $newTableField->Id
                    ]);
                }
            }
        }

        // Delete Section
        $tableFieldsToDelete = $this->tableFieldRepository->getByAttributes(
            [
                ['column' => 'Id', 'operand' => '=', 'value' => $request->get('tableFieldIdsToDelete')]
            ],
            ['companyTableFields.company']
        );

        foreach ($tableFieldsToDelete as $tableFieldToDelete) {
            $this->tableFieldRepository->findByIdAndDelete($tableFieldToDelete->Id);
            $this->companyTableFieldRepository->deleteByAttributes([
                ['column' => 'TableFieldId', 'operand' => '=', 'value' => $tableFieldToDelete->Id]
            ]);
        }

        // Update Section
        $requestedUpdatedTableFields = $request->get('updatedTableFields');
        $updatedTableFieldsRows = $this->tableFieldRepository->getByAttributes(
            [
                ['column' => 'Id', 'operand' => '=', 'value' => collect($requestedUpdatedTableFields)->pluck('Id')->toArray()]
            ],
            ['companyTableFields.company']
        )->groupBy('Id');

        foreach ($requestedUpdatedTableFields as $requestedUpdatedTableField) {
            $existingTableFieldData = $updatedTableFieldsRows[$requestedUpdatedTableField['Id']][0];

            // Update TableField Entry
            $this->tableFieldRepository->findByIdAndUpdate($existingTableFieldData->Id, [
                'Name' => $requestedUpdatedTableField['Name'],
                'DataType' => $requestedUpdatedTableField['DataType'],
                'Type' => $requestedUpdatedTableField['Type'],
                'Length' => $requestedUpdatedTableField['Length'],
                'Nullable' => $requestedUpdatedTableField['Nullable'],
                'Unique' => $requestedUpdatedTableField['Unique'],
                'PrimaryKey' => $requestedUpdatedTableField['PrimaryKey'],
                'DefaultValue' => $requestedUpdatedTableField['DefaultValue'],
                'AutoIncrement' => $requestedUpdatedTableField['AutoIncrement'],
                'SortOrder' => $requestedUpdatedTableField['SortOrder'],
            ]);

            /*-------------- Rename Column Start -----------------*/
            /*$hasRenameOperation = in_array('Name', $requestedUpdatedTableField['updatedSections']);
            if ($hasRenameOperation) {
                if (in_array($requestedUpdatedTableField['Type'], ['Server', 'Both'])) {
                    $tableFieldSpecificDatabases = $existingTableFieldData->companyTableFields->pluck('company.DatabaseName')->toArray();
                    $selectedDatabasesForUpdate = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableFieldSpecificDatabases);
                    foreach ($selectedDatabasesForUpdate as $database) {
                        $newColumDefinition = $this->makeFieldDefinitionArray($requestedUpdatedTableField);
                        $sql = MysqlQueryGenerator::getRenameColumnSql($database, $table->Name, $existingTableFieldData->Name, $newColumDefinition);
                        $sqlQueries[] = $sql;
                    }
                }
            }*/
            /*-------------- Rename Column End -----------------*/

            /**
             * Skip Other Column Operations If Rename Operation is present
             */

            /*if (
                !$hasRenameOperation &&
                (
                    in_array('DataType', $requestedUpdatedTableField['updatedSections']) ||
                    in_array('Length', $requestedUpdatedTableField['updatedSections']) ||
                    in_array('Nullable', $requestedUpdatedTableField['updatedSections']) ||
                    in_array('Default', $requestedUpdatedTableField['updatedSections']) ||
                    in_array('AutoIncrement', $requestedUpdatedTableField['updatedSections'])
                )
            ) {
                if (in_array($requestedUpdatedTableField['Type'], ['Server', 'Both'])) {
                    $tableFieldSpecificDatabases = $existingTableFieldData->companyTableFields->pluck('company.DatabaseName')->toArray();
                    $selectedDatabasesForColumnModification = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableFieldSpecificDatabases);
                    foreach ($selectedDatabasesForColumnModification as $database) {
                        $requestedColumDefinition = $this->makeFieldDefinitionArray($requestedUpdatedTableField);
                        $sql = MysqlQueryGenerator::getModifyColumnSql($database, $table->Name, $requestedColumDefinition);
                        $sqlQueries[] = $sql;
                    }
                }
            }

            if (
                !$hasRenameOperation &&
                (
                    in_array('PrimaryKey', $requestedUpdatedTableField['updatedSections']) ||
                    in_array('Unique', $requestedUpdatedTableField['updatedSections'])
                )
            ) {
                if (in_array($requestedUpdatedTableField['Type'], ['Server', 'Both'])) {
                    $tableFieldSpecificDatabases = $existingTableFieldData->companyTableFields->pluck('company.DatabaseName')->toArray();
                    $selectedDatabasesForColumnModification = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableFieldSpecificDatabases);

                    if (in_array('PrimaryKey', $requestedUpdatedTableField['updatedSections'])) {
                        foreach ($selectedDatabasesForColumnModification as $database) {
                            if ($requestedUpdatedTableField['PrimaryKey']) {
                                // Add Primary Key
                                $sql = MysqlQueryGenerator::getAddPrimaryKeySql($database, $table->Name, $requestedUpdatedTableField['Name']);
                            } else {
                                // Delete Primary Key
                                $sql = MysqlQueryGenerator::getRemovePrimaryKeySql($database, $table->Name, $requestedUpdatedTableField['Name']);
                            }

                            $sqlQueries[] = $sql;
                        }
                    }

                    if (in_array('Unique', $requestedUpdatedTableField['updatedSections'])) {
                        foreach ($selectedDatabasesForColumnModification as $database) {
                            if ($requestedUpdatedTableField['Unique']) {
                                // Add Unique Key
                                $sql = MysqlQueryGenerator::getAddUniqueKeySql($database, $table->Name, $requestedUpdatedTableField['Name']);
                            } else {
                                // Delete Unique Key
                                $sql = MysqlQueryGenerator::getRemoveUniqueKeySql($database, $table->Name, $requestedUpdatedTableField['Name']);
                            }

                            $sqlQueries[] = $sql;
                        }
                    }
                }
            }*/

            /*-------------- Add/Delete Field in/from Company Start -----------------*/
            // Add or delete columns with
            if (in_array('Company', $requestedUpdatedTableField['updatedSections'])) {
                $existingTableFieldCompanyIds = $existingTableFieldData->companyTableFields->pluck('company.Id')->toArray();
                $requestedCompanies = $this->companyRepository->getByAttributes([
                    ['column' => 'Id', 'operand' => '=', 'value' => $requestedUpdatedTableField['companies']]
                ]);
                $requestedCompanyIds = $requestedCompanies->pluck('Id')->toArray();
                // Add CompanyTableField Entry
                $newCompanyIds = array_diff($requestedCompanyIds, $existingTableFieldCompanyIds);
                foreach ($newCompanyIds as $companyId) {
                    $this->companyTableFieldRepository->create([
                        'CompanyId' => $companyId,
                        'TableFieldId' => $existingTableFieldData->Id
                    ]);
                }

                // Remove CompanyTableField Entry
                $removedCompanyIds = array_diff($existingTableFieldCompanyIds, $requestedCompanyIds);
                $this->companyTableFieldRepository->deleteByAttributes([
                    ['column' => 'TableFieldId', 'operand' => '=', 'value' => $existingTableFieldData->Id],
                    ['column' => 'CompanyId', 'operand' => '=', 'value' => $removedCompanyIds],
                ]);

                /*if (in_array($requestedUpdatedTableField['Type'], ['Server', 'Both'])) {
                    $existingTableFieldDatabases = $existingTableFieldData->companyTableFields->pluck('company.DatabaseName')->toArray();
                    $requestedDatabases = $requestedCompanies->pluck('DatabaseName')->toArray();

                    // Add Column to Databases
                    $newDatabases = array_diff($requestedDatabases, $existingTableFieldDatabases);
                    foreach ($newDatabases as $newDatabase) {
                        $newColumDefinition = $this->makeFieldDefinitionArray($requestedUpdatedTableField);
                        $sql = MysqlQueryGenerator::getAddColumnSql($newDatabase, $table->Name, $newColumDefinition);
                        $sqlQueries[] = $sql;
                    }

                    // Drop Column from Databases
                    $removedDatabases = array_diff($existingTableFieldDatabases, $requestedDatabases);
                    foreach ($removedDatabases as $removedDatabase) {
                        $sql = MysqlQueryGenerator::getDeleteColumnSql($removedDatabase, $table->Name, $requestedUpdatedTableField['Name']);
                        $sqlQueries[] = $sql;
                    }
                }*/
            }
            /*-------------- Add/Delete Field in/from Company End -----------------*/

            /*-------------- Type Changed Start----------------- */
            /**
             * Was a client Type Field, Now became server or both then need to add in databases
             * Was a server or both type Field, Now become Client then need to remove from databases
             */
            /*if (in_array('Type', $requestedUpdatedTableField['updatedSections'])) {
                if (in_array($existingTableFieldData->Type, ['Server', 'Both']) && $requestedUpdatedTableField['Type'] == 'Client') {
                    // Remove column from Databases
                    $tableFieldSpecificDatabases = $this->getDatabaseNamesByCompanyIds($requestedUpdatedTableField['companies']);
                    $selectedDatabases = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableFieldSpecificDatabases);
                    foreach ($selectedDatabases as $database) {
                        $sql = MysqlQueryGenerator::getDeleteColumnSql($database, $table->Name, $requestedUpdatedTableField['Name']);
                        $sqlQueries[] = $sql;
                    }
                }

                if ($existingTableFieldData->Type == 'Client' && in_array($requestedUpdatedTableField['Type'], ['Server', 'Both'])) {
                    // Add Columns in databases
                    $tableFieldSpecificDatabases = $this->getDatabaseNamesByCompanyIds($requestedUpdatedTableField['companies']);
                    $selectedDatabases = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableFieldSpecificDatabases);
                    foreach ($selectedDatabases as $database) {
                        $sql = MysqlQueryGenerator::getAddColumnSql($database, $table->Name, $this->makeFieldDefinitionArray($requestedUpdatedTableField));
                        $sqlQueries[] = $sql;
                    }
                }
            }*/
            /*-------------- Type Changed End----------------- */
        }


        return new ServiceDto("TableFields Operation Save without Executing Finished Successfully.", 200, []);
    }
}
