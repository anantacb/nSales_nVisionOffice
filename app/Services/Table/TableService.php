<?php

namespace App\Services\Table;


use App\Contracts\ServiceDto;
use App\Helpers\Sql\MysqlQueryGenerator;
use App\Helpers\Sql\SqliteQueryGenerator;
use App\Helpers\SqlFormatter;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyTable\CompanyTableRepositoryInterface;
use App\Repositories\Eloquent\Office\Module\ModuleRepositoryInterface;
use App\Repositories\Eloquent\Office\Table\TableRepositoryInterface;
use App\Repositories\Eloquent\Office\TableField\TableFieldRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TableService implements TableServiceInterface
{
    protected TableRepositoryInterface $tableRepository;
    protected CompanyTableRepositoryInterface $companyTableRepository;
    protected TableFieldRepositoryInterface $tableFieldRepository;
    protected ModuleRepositoryInterface $moduleRepository;
    protected CompanyRepositoryInterface $companyRepository;

    public function __construct(
        TableRepositoryInterface        $tableRepository,
        TableFieldRepositoryInterface   $tableFieldRepository,
        ModuleRepositoryInterface       $moduleRepository,
        CompanyRepositoryInterface      $companyRepository,
        CompanyTableRepositoryInterface $companyTableRepository
    )
    {
        $this->tableRepository = $tableRepository;
        $this->tableFieldRepository = $tableFieldRepository;
        $this->moduleRepository = $moduleRepository;
        $this->companyRepository = $companyRepository;
        $this->companyTableRepository = $companyTableRepository;
    }

    public function getTables(Request $request): ServiceDto
    {
        $request = $request->all();
        $request['relations'] = [
            ["name" => "companyTables.Company", "columns" => ['Id', 'Name', 'CompanyName']],
        ];
        $tables = $this->tableRepository->paginatedData($request);
        return new ServiceDto("Tables retrieved!!!", 200, $tables);
    }

    public function getCreateTablePreview(Request $request): ServiceDto
    {
        $response = $this->generateCreateTableSqlQueries($request);
        if ($response['success']) {
            $response['data']['sqlPreviews'] = collect($response['data']['sqlPreviews'])->map(function ($sql) {
                return SqlFormatter::format($sql);
            })->toArray();
            $response['data']['sqlitePreviews'] = collect($response['data']['sqlPreviews'])->map(function ($sql) {
                return SqlFormatter::format($sql);
            })->toArray();
            unset($response['data']['databases']);
            return new ServiceDto("Sql Queries Generated Successfully", 200, $response['data']);
        } else {
            return new ServiceDto($response['message'], $response['statusCode'], $response['errors']);
        }
    }

    private function generateCreateTableSqlQueries(Request $request): array
    {
        $module = $this->moduleRepository->findById($request->get('module'));
        $moduleEnabledCompanies = $this->moduleRepository->getRelationData($module, 'companies');
        $moduleEnabledCompanyIds = $moduleEnabledCompanies->pluck('Id')->toArray();
        $selectedDBNames = $moduleEnabledCompanies->pluck('DatabaseName')->toArray();

        if ($request->get('selectedCompanies')) {
            $moduleNotEnabledCompanyIds = array_diff($request->get('selectedCompanies'), $moduleEnabledCompanyIds);
            $moduleNotEnabledCompanyIdsNames = $this->companyRepository->getByAttributes([
                ['column' => 'Id', 'operand' => '=', 'value' => $moduleNotEnabledCompanyIds]
            ])->mapWithKeys(function ($company) {
                return [$company->Id => $company->Name];
            })->all();
            if ($moduleNotEnabledCompanyIds) {
                $errors = [];
                foreach ($moduleNotEnabledCompanyIds as $moduleNotEnabledCompanyId) {
                    $errors['selectedCompanies'][] = "Module not enabled for " . $moduleNotEnabledCompanyIdsNames[$moduleNotEnabledCompanyId];
                }
                return [
                    'success' => false,
                    'statusCode' => 422,
                    'message' => "Module Error!!!",
                    'errors' => $errors
                ];
            } else {
                $selectedCompanies = $this->companyRepository->getByAttributes([
                    ['column' => 'Id', 'operand' => '=', 'value' => $request->get('selectedCompanies')]
                ]);
                $selectedDBNames = $selectedCompanies->pluck('DatabaseName')->toArray();
            }
        }

        switch ($request->get('database')) {
            case 'Company':
                if (!$request->get('selectedCompanies')) {
                    $selectedDBNames = array_merge($selectedDBNames, ['NVISION_TEMPLATE']);
                }
                break;
            case 'Office':
                $selectedDBNames = ['NVISION_OFFICE'];
                break;
            case 'Both':
                if (!$request->get('selectedCompanies')) {
                    $selectedDBNames = array_merge($selectedDBNames, ['NVISION_TEMPLATE', 'NVISION_OFFICE']);
                }
                break;
        }

        $data = [
            'sqlPreview' => false,
            'sqlPreviewMessage' => "",
            'sqlPreviews' => [],
            'sqlitePreview' => false,
            'sqlitePreviewMessage' => "",
            'sqlitePreviews' => [],
            'databases' => $selectedDBNames
        ];

        $tableName = $request->get('name');
        if (in_array($request->get('type'), ['Server', 'Both'])) {
            $columnDefinitions = config('initialColumnDefinitionsMysql');
            $sqlPreviews = [];
            foreach ($selectedDBNames as $databaseName) {
                $sqlPreviews[] = MysqlQueryGenerator::getCreateTableSql($databaseName, $tableName, $columnDefinitions);
            }
            $data['sqlPreview'] = true;
            $data['sqlPreviews'] = $sqlPreviews;
            if ($request->get('type') == 'Server') {
                $data['sqlitePreviewMessage'] = "No Sql Generated as Type is `Server`";
            } elseif ($request->get('type') == 'Both') {
                $columnDefinitions = config('initialColumnDefinitionsSqlite');
                $data['sqlitePreviews'] = [SqliteQueryGenerator::getCreateTableSql($tableName, $columnDefinitions)];
                $data['sqlitePreview'] = true;
            }
        } elseif ($request->get('type') == 'Client') {
            $columnDefinitions = config('initialColumnDefinitionsSqlite');
            $data['sqlitePreviews'] = [SqliteQueryGenerator::getCreateTableSql($tableName, $columnDefinitions)];
            $data['sqlitePreview'] = true;
            $data['sqlPreviewMessage'] = "No Sql generated as Type is `Client`";
        }

        return [
            'success' => true,
            'message' => "Sql Queries Generated Successfully.",
            'data' => $data
        ];
    }

    public function createTableSaveAndExecute(Request $request): ServiceDto
    {
        $insertTableResponse = $this->insertDataIntoTableAndTableField($request);
        if (!$insertTableResponse['success']) {
            return new ServiceDto($insertTableResponse['message'], 500);
        }

        $generateTableSqlResponse = $this->generateCreateTableSqlQueries($request);
        if ($generateTableSqlResponse['success']) {
            foreach ($generateTableSqlResponse['data']['sqlPreviews'] as $sql) {
                try {
                    DB::statement($sql);
                } catch (Exception $exception) {
                    if (App::environment('production')) {
                        $this->deleteTableAndRelationalData($insertTableResponse['data']['table']);
                        $this->dropTablesFromDatabases($insertTableResponse['data']['table'], $generateTableSqlResponse['data']['databases']);
                        return new ServiceDto($exception->getMessage(), 500);
                    }
                    Log::error("Create Table query execution failed Query: {$sql}");
                }
            }
            return new ServiceDto("Table Created and Added into Databases Successfully.", 200);
        } else {
            return new ServiceDto($generateTableSqlResponse['message'], $generateTableSqlResponse['statusCode'], $generateTableSqlResponse['errors']);
        }
    }

    private function insertDataIntoTableAndTableField(Request $request): array
    {
        DB::beginTransaction();
        try {
            // Insert into `Table` table
            $table = $this->tableRepository->create([
                'ModuleId' => $request->get('module'),
                //'CompanyId' => $request->get('CompanyId'),
                'Name' => $request->get('name'),
                'Type' => $request->get('type'),
                'Database' => $request->get('database'),
                'Note' => $request->get('note'),
                'Disabled' => $request->get('disabled'),
                //'Deleted' => $request->get('Deleted'),
                'MappingTableName' => '',
                //'AutoMapping' => $request->get('AutoMapping'),
                'Version' => 0,
                'ClientSync' => $request->get('clientSync') ?? "",
                'AutoNumbering' => $request->get('autoNumbering'),
                'SqlTruncate' => $request->get('sqlTruncate'),
                'EnableSqlTruncate' => $request->get('enableTruncate'),
                'SqlSeed' => $request->get('sqlSeed')
            ]);

            // Insert into `CompanyTable` table if for specific company
            if ($request->get('selectedCompanies')) {
                foreach ($request->get('selectedCompanies') as $selectedCompanyId) {
                    $this->companyTableRepository->create([
                        'CompanyId' => $selectedCompanyId,
                        'TableId' => $table->Id
                    ]);
                }
            }

            // Insert into `TableField` table
            $columnDefinitions = config('initialColumnDefinitionsMysql');
            foreach ($columnDefinitions as $columnDefinition) {
                $this->tableFieldRepository->create(
                    [
                        'TableId' => $table->Id,
                        'SortOrder' => $columnDefinition['SortOrder'],
                        'Name' => $columnDefinition['Name'],
                        'DataType' => $columnDefinition['DataType'],
                        'Type' => $request->get('type'),
                        'Length' => $columnDefinition['Length'],
                        'Nullable' => $columnDefinition['Nullable'],
                        'Unique' => $columnDefinition['Unique'],
                        'PrimaryKey' => $columnDefinition['PrimaryKey'],
                        'DefaultValue' => $columnDefinition['DefaultValue'],
                        'AutoIncrement' => $columnDefinition['AutoIncrement']
                    ]
                );
            }
            DB::commit();
            return [
                'success' => true,
                'data' => [
                    'table' => $table
                ]
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $exception->getMessage()
            ];
        }
    }

    private function deleteTableAndRelationalData($table)
    {
        $this->tableRepository->findByIdAndDelete($table->Id);
        $this->tableFieldRepository->deleteByAttributes(
            [
                ['column' => 'TableId', 'operand' => '=', 'value' => $table->Id]
            ]
        );
        $this->companyTableRepository->deleteByAttributes(
            [
                ['column' => 'TableId', 'operand' => '=', 'value' => $table->Id]
            ]
        );
    }

    private function dropTablesFromDatabases($table, $databases)
    {
        foreach ($databases as $database) {
            $sql = MysqlQueryGenerator::getDropTableSql($database, $table->Name);
            try {
                DB::statement($sql);
            } catch (Exception $exception) {
                Log::error("DROP Table query execution failed \nDB: {$database} \nTable: {$table->Name} \nMessage: {$exception->getMessage()}");
            }
        }
    }

    public function createTableSaveWithoutExecuting(Request $request): ServiceDto
    {
        $response = $this->insertDataIntoTableAndTableField($request);
        if ($response['success']) {
            return new ServiceDto('Table and TableField insert successful.', 200, []);
        } else {
            return new ServiceDto($response['message'], 500);
        }
    }

    public function deleteTable(Request $request): ServiceDto
    {
        $table = $this->tableRepository->firstByAttributes(
            [
                ['column' => 'Id', 'operand' => '=', 'value' => $request->get('tableId')]
            ],
            ['companyTables.company', 'module.companies']
        );

        $isCompanySpecificTable = $table->companyTables->count() > 0;

        if ($isCompanySpecificTable) { // Company Specific Table
            $selectedDBNames = $table->companyTables->pluck('company.DatabaseName')->toArray();
        } else {
            $selectedDBNames = $table->module->companies->pluck('DatabaseName')->toArray();
        }

        switch ($table->Database) {
            case 'Company':
                if (!$isCompanySpecificTable) {
                    $selectedDBNames = array_merge($selectedDBNames, ['NVISION_TEMPLATE']);
                }
                break;
            case 'Office':
                $selectedDBNames = ['NVISION_OFFICE'];
                break;
            case 'Both':
                if (!$isCompanySpecificTable) {
                    $selectedDBNames = array_merge($selectedDBNames, ['NVISION_TEMPLATE', 'NVISION_OFFICE']);
                }
                break;
        }
        $this->deleteTableAndRelationalData($table);
        $this->dropTablesFromDatabases($table, $selectedDBNames);

        return new ServiceDto("Table Deleted Successfully.", 200);
    }

    public function getDetails(Request $request): ServiceDto
    {
        $table = $this->tableRepository->firstByAttributes(
            [
                ['column' => 'Id', 'operand' => '=', 'value' => $request->get('tableId')]
            ],
            ['companyTables.company', 'module.companies']
        );

        return new ServiceDto("Table Deleted Successfully.", 200, $table);
    }

    public function getByModule(Request $request): ServiceDto
    {
        $table = $this->tableRepository->getByAttributes(
            [
                ['column' => 'ModuleId', 'operand' => '=', 'value' => $request->get('moduleId')]
            ],
            [], ['Name', 'Id']
        );

        return new ServiceDto("Tables By Module Retrieved Successfully.", 200, $table);
    }
}
