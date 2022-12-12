<?php

namespace App\Services\Table;


use App\Contracts\ServiceDto;
use App\Helpers\Sql\MysqlQueryGenerator;
use App\Helpers\Sql\SqliteQueryGenerator;
use App\Helpers\SqlFormatter;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\Module\ModuleRepositoryInterface;
use App\Repositories\Eloquent\Office\Table\TableFieldRepositoryInterface;
use App\Repositories\Eloquent\Office\Table\TableRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TableService implements TableServiceInterface
{
    protected TableRepositoryInterface $tableRepository;
    protected TableFieldRepositoryInterface $tableFieldRepository;
    protected ModuleRepositoryInterface $moduleRepository;
    protected CompanyRepositoryInterface $companyRepository;

    public function __construct(
        TableRepositoryInterface      $tableRepository,
        TableFieldRepositoryInterface $tableFieldRepository,
        ModuleRepositoryInterface     $moduleRepository,
        CompanyRepositoryInterface    $companyRepository
    )
    {
        $this->tableRepository = $tableRepository;
        $this->tableFieldRepository = $tableFieldRepository;
        $this->moduleRepository = $moduleRepository;
        $this->companyRepository = $companyRepository;
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
                return new ServiceDto("Module Error!!!", 422, $errors);
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
        ];

        $tableName = $request->get('name');
        if (in_array($request->get('type'), ['Server', 'Both'])) {
            $columnDefinitions = config('initialColumnDefinitionsMysql');
            $sqlPreviews = [];
            foreach ($selectedDBNames as $databaseName) {
                $sqlPreviews[] = SqlFormatter::format(MysqlQueryGenerator::getCreateTableSql($databaseName, $tableName, $columnDefinitions));
            }
            $data['sqlPreview'] = true;
            $data['sqlPreviews'] = $sqlPreviews;
            if ($request->get('type') == 'Server') {
                $data['sqlitePreviewMessage'] = "No Sql Generated as Type is `Server`";
            } elseif ($request->get('type') == 'Both') {
                $columnDefinitions = config('initialColumnDefinitionsSqlite');
                $data['sqlitePreviews'] = [SqlFormatter::format(SqliteQueryGenerator::getCreateTableSql($tableName, $columnDefinitions))];
                $data['sqlitePreview'] = true;
            }
        } elseif ($request->get('type') == 'Client') {
            $columnDefinitions = config('initialColumnDefinitionsSqlite');
            $data['sqlitePreviews'] = [SqlFormatter::format(SqliteQueryGenerator::getCreateTableSql($tableName, $columnDefinitions))];
            $data['sqlitePreview'] = true;
            $data['sqlPreviewMessage'] = "No Sql generated as Type is `Client`";
        }

        return new ServiceDto("Tables retrieved!!!", 200, $data);
    }

    public function createTableSaveAndExecute(Request $request): ServiceDto
    {
    }

    public function createTableSaveWithoutExecuting(Request $request): ServiceDto
    {
        DB::beginTransaction();

        try {
            $table = $this->tableRepository->create([
                'ModuleId' => $request->get('moduleId'),
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
                'EnableSqlTruncate' => $request->get('enableSqlTruncate'),
                'SqlSeed' => $request->get('sqlSeed')
            ]);

            $columnDefinitions = config('initialColumnDefinitionsMysql');

            foreach ($columnDefinitions as $columnDefinition) {
                $this->tableFieldRepository->create(
                    [
                        'TableId' => $table->Id,
                        'SortOrder' => $columnDefinition['sort_order'],
                        'Name' => $columnDefinition['name'],
                        'DataType' => $columnDefinition['data_type'],
                        'Type' => $request->get('type'),
                        'Length' => $columnDefinition['length'],
                        'Nullable' => $columnDefinition['nullable'],
                        'Unique' => $columnDefinition['unique_key'],
                        'PrimaryKey' => $columnDefinition['primary_key'],
                        'DefaultValue' => $columnDefinition['default'],
                        'AutoIncrement' => $columnDefinition['auto_increment'],
                        'Disabled' => $columnDefinition['disabled']
                    ]
                );
            }
            DB::commit();
            return new ServiceDto('Operation Successful', 200, []);
        } catch (Exception $exception) {
            DB::rollBack();
            return new ServiceDto("Table Creation Failed!!!", 500, [
                'message' => $exception->getMessage()
            ]);
        }
    }
}
