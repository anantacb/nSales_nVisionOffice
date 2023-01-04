<?php

namespace App\Services\TableField;


use App\Contracts\ServiceDto;
use App\Helpers\Sql\MysqlQueryGenerator;
use App\Helpers\SqlFormatter;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\Table\CompanyTableFieldRepositoryInterface;
use App\Repositories\Eloquent\Office\Table\CompanyTableRepositoryInterface;
use App\Repositories\Eloquent\Office\Table\TableFieldRepositoryInterface;
use App\Repositories\Eloquent\Office\Table\TableRepositoryInterface;
use Exception;
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
                    $selectedDatabases = [];
                    // This Field Is CompanySpecific
                    if ($newField['companies']) {
                        $selectedDatabases = $this->companyRepository->getByAttributes([
                            ['column' => 'Id', 'operand' => '=', 'value' => $newField['companies']]
                        ])->pluck('DatabaseName')->toArray();
                    } else {
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
                    }

                    foreach ($selectedDatabases as $database) {
                        $this->makeFieldDefinitionArray($newField);
                        $sql = MysqlQueryGenerator::getAddColumnSql($database, $table->Name, $this->makeFieldDefinitionArray($newField));
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
                    if ($tableFieldToDelete->companyTableFields->count()) {
                        // Company Specific Table Field
                        $selectedDatabases = $tableFieldToDelete->companyTableFields->pluck('company.DatabaseName')->toArray();
                    } else {
                        // Company Specific Table
                        if ($companyTableDatabases) {
                            $selectedDatabases = $companyTableDatabases;
                        } else {
                            // For all module Enabled Companies
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
                    }
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
                if (in_array('Name', $requestedUpdatedTableField['updatedSections'])) {
                    /*Rename Column*/
                    $selectedDatabasesForUpdate = [];
                    if (in_array($requestedUpdatedTableField['Type'], ['Server', 'Both'])) {
                        $existingTableFieldData = $updatedTableFieldsRows[$requestedUpdatedTableField['Id']][0];
                        if ($existingTableFieldData->companyTableFields->count()) {
                            // Company Specific Table Field
                            $selectedDatabasesForUpdate = $existingTableFieldData->companyTableFields->pluck('company.DatabaseName')->toArray();
                        } else {
                            // Company Specific Table
                            if ($companyTableDatabases) {
                                $selectedDatabasesForUpdate = $companyTableDatabases;
                            } else {
                                // For all module Enabled Companies
                                switch ($table->Database) {
                                    case 'Company':
                                        $selectedDatabasesForUpdate = array_merge($tableModuleCompanyDatabases, ['NVISION_TEMPLATE']);
                                        break;
                                    case 'Office':
                                        $selectedDatabasesForUpdate = ['NVISION_OFFICE'];
                                        break;
                                    case 'Both':
                                        $selectedDatabasesForUpdate = array_merge($tableModuleCompanyDatabases, ['NVISION_TEMPLATE', 'NVISION_OFFICE']);
                                        break;
                                }
                            }
                        }
                        foreach ($selectedDatabasesForUpdate as $database) {
                            $newColumDefinition = $this->makeFieldDefinitionArray($requestedUpdatedTableField);
                            $sql = MysqlQueryGenerator::getRenameColumnSql($database, $table->Name, $existingTableFieldData->Name, $newColumDefinition);
                            $sqlQueries[] = $sql;
                        }
                    }
                }
//                dd($requestedUpdatedTableField, $updatedTableFieldsRows[$requestedUpdatedTableField['Id']]);
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

    private function makeFieldDefinitionArray($field): array
    {
        return [
            'name' => $field['Name'],
            'data_type' => $field['DataType'],
            'length' => $field['Length'],
            'auto_increment' => $field['AutoIncrement'],
            'nullable' => $field['Nullable'],
            'default' => $field['DefaultValue'],
            'primary_key' => $field['PrimaryKey'],
            'unique_key' => $field['Unique'],
            'sort_order' => $field['SortOrder']
        ];
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
            $newTableField = $this->tableFieldRepository->create(
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

            $selectedDatabases = [];

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
            }

            foreach ($selectedDatabases as $database) {
                $this->makeFieldDefinitionArray($newField);
                $sql = MysqlQueryGenerator::getAddColumnSql($database, $table->Name, $this->makeFieldDefinitionArray($newField));
                $sqlQueries[] = $sql;
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
            if ($tableFieldToDelete->companyTableFields->count()) {
                $selectedDatabases = $tableFieldToDelete->companyTableFields->pluck('company.DatabaseName')->toArray();
            } else {
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
            }

            foreach ($selectedDatabases as $database) {
                $sql = MysqlQueryGenerator::getDeleteColumnSql($database, $table->Name, $tableFieldToDelete->Name);
                $sqlQueries[] = $sql;
            }

            $this->tableFieldRepository->findByIdAndDelete($tableFieldToDelete->Id);
            $this->companyTableFieldRepository->deleteByAttributes([
                ['column' => 'TableFieldId', 'operand' => '=', 'value' => $tableFieldToDelete->Id]
            ]);
        }

        if (in_array($table->Type, ['Server', 'Both'])) {
            foreach ($sqlQueries as $sql) {
                try {
                    DB::statement($sql);
                } catch (Exception $exception) {
                    if (App::environment('production')) {
                        return new ServiceDto($exception->getMessage(), 500);
                    }
                    Log::error("Query execution failed Query: {$sql}");
                }
            }
        }

        return new ServiceDto("TableFields Operation Save And Execute Finished Successfully.", 200, []);
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

        $sqlQueries = [];

        // Add Section
        foreach ($request->get('newFields') as $newField) {
            // Entry In TableField Table
            $newTableField = $this->tableFieldRepository->create(
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

            $selectedDatabases = [];

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
            }

            foreach ($selectedDatabases as $database) {
                $this->makeFieldDefinitionArray($newField);
                $sql = MysqlQueryGenerator::getAddColumnSql($database, $table->Name, $this->makeFieldDefinitionArray($newField));
                $sqlQueries[] = $sql;
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
            if ($tableFieldToDelete->companyTableFields->count()) {
                $selectedDatabases = $tableFieldToDelete->companyTableFields->pluck('company.DatabaseName')->toArray();
            } else {
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
            }

            foreach ($selectedDatabases as $database) {
                $sql = MysqlQueryGenerator::getDeleteColumnSql($database, $table->Name, $tableFieldToDelete->Name);
                $sqlQueries[] = $sql;
            }

            $this->tableFieldRepository->findByIdAndDelete($tableFieldToDelete->Id);
            $this->companyTableFieldRepository->deleteByAttributes([
                ['column' => 'TableFieldId', 'operand' => '=', 'value' => $tableFieldToDelete->Id]
            ]);
        }

        return new ServiceDto("TableFields Operation Save without Executing Finished Successfully.", 200, []);
    }
}
