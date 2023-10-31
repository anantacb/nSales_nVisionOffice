<?php

namespace App\Services\TableIndex;


use App\Contracts\ServiceDto;
use App\Helpers\Sql\MysqlQueryGenerator;
use App\Helpers\SqlFormatter;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyTable\CompanyTableRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyTableField\CompanyTableFieldRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyTableIndex\CompanyTableIndexRepositoryInterface;
use App\Repositories\Eloquent\Office\Table\TableRepositoryInterface;
use App\Repositories\Eloquent\Office\TableField\TableFieldRepositoryInterface;
use App\Repositories\Eloquent\Office\TableIndex\TableIndexRepositoryInterface;
use App\Services\Traits\TableHelperTrait;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TableIndexService implements TableIndexServiceInterface
{
    use TableHelperTrait;

    protected TableRepositoryInterface $tableRepository;
    protected CompanyTableRepositoryInterface $companyTableRepository;
    protected TableFieldRepositoryInterface $tableFieldRepository;
    protected TableIndexRepositoryInterface $tableIndexRepository;
    protected CompanyRepositoryInterface $companyRepository;

    protected CompanyTableFieldRepositoryInterface $companyTableFieldRepository;
    protected CompanyTableIndexRepositoryInterface $companyTableIndexRepository;

    public function __construct(
        TableRepositoryInterface             $tableRepository,
        TableFieldRepositoryInterface        $tableFieldRepository,
        CompanyRepositoryInterface           $companyRepository,
        CompanyTableRepositoryInterface      $companyTableRepository,
        CompanyTableFieldRepositoryInterface $companyTableFieldRepository,
        TableIndexRepositoryInterface        $tableIndexRepository,
        CompanyTableIndexRepositoryInterface $companyTableIndexRepository
    )
    {
        $this->tableRepository = $tableRepository;
        $this->tableFieldRepository = $tableFieldRepository;
        $this->tableIndexRepository = $tableIndexRepository;
        $this->companyRepository = $companyRepository;
        $this->companyTableRepository = $companyTableRepository;
        $this->companyTableFieldRepository = $companyTableFieldRepository;
        $this->companyTableIndexRepository = $companyTableIndexRepository;
    }

    public function getTableIndices(Request $request): ServiceDto
    {
        $tableId = $request->get('TableId');
        $relations = [
            'companyTableIndices' => function ($q) {
                $q->with([
                    'company' => function ($q1) {
                        $q1->select(['Id', 'Name', 'CompanyName']);
                    }
                ])->select(['Id', 'CompanyId', 'TableIndexId']);
            }
        ];

        $tableIndices = $this->tableIndexRepository->getByAttributes([
            ['column' => 'TableId', 'operand' => '=', 'value' => $tableId]
        ], $relations);

        return new ServiceDto("TableIndices Retrieved Successfully.", 200, $tableIndices);
    }

    public function getTableIndicesOperationPreviews(Request $request): ServiceDto
    {
        $tableId = $request->get('TableId');
        $table = $this->tableRepository->firstByAttributes(
            [
                ['column' => 'Id', 'operand' => '=', 'value' => $tableId]
            ],
            ['companyTables.company', 'module.companies']
        );

        $data = [
            'sqlPreview' => false,
            'sqlPreviewMessage' => "No query generated for execution.",
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
            foreach ($request->get('newIndices') as $newIndex) {
                if (in_array($newIndex['Type'], ['Server', 'Both'])) {
                    // This Field Is CompanySpecific
                    $tableIndexSpecificDatabases = $this->getDatabaseNamesByCompanyIds($newIndex['companies']);
                    $selectedDatabases = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableIndexSpecificDatabases);

                    foreach ($selectedDatabases as $database) {
                        $sql = MysqlQueryGenerator::getAddIndexSql($database, $table->Name, $newIndex);
                        $sqlQueries[] = $sql;
                    }
                }
            }

            // Delete Section
            $tableIndicesToDelete = $this->tableIndexRepository->getByAttributes(
                [
                    ['column' => 'Id', 'operand' => '=', 'value' => $request->get('tableIndexIdsToDelete')]
                ],
                ['companyTableIndices.company']
            );

            foreach ($tableIndicesToDelete as $tableIndexToDelete) {
                if (in_array($tableIndexToDelete->Type, ['Server', 'Both'])) {
                    // Company Specific Index
                    $tableIndexSpecificDatabases = $tableIndexToDelete->companyTableIndices->pluck('company.DatabaseName')->toArray();
                    $selectedDatabases = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableIndexSpecificDatabases);
                    foreach ($selectedDatabases as $database) {
                        $sql = MysqlQueryGenerator::getDeleteIndexSql($database, $table->Name, $tableIndexToDelete->Name);
                        $sqlQueries[] = $sql;
                    }
                }
            }

            // Update Section
            $requestedUpdatedTableIndices = $request->get('updatedTableIndices');
            $updatedTableIndicesRows = $this->tableIndexRepository->getByAttributes(
                [
                    ['column' => 'Id', 'operand' => '=', 'value' => collect($requestedUpdatedTableIndices)->pluck('Id')->toArray()]
                ],
                ['companyTableIndices.company']
            )->groupBy('Id');

            foreach ($requestedUpdatedTableIndices as $requestedUpdatedTableIndex) {
                $existingTableIndexData = $updatedTableIndicesRows[$requestedUpdatedTableIndex['Id']][0];

                $tableIndexSpecificDatabases = $existingTableIndexData->companyTableIndices->pluck('company.DatabaseName')->toArray();
                $selectedDatabasesForUpdate = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableIndexSpecificDatabases);

                // Delete Existing Index from Databases
                if (in_array($existingTableIndexData->Type, ['Server', 'Both'])) {
                    foreach ($selectedDatabasesForUpdate as $database) {
                        $sql = MysqlQueryGenerator::getDeleteIndexSql($database, $table->Name, $existingTableIndexData->Name);
                        $sqlQueries[] = $sql;
                    }
                }
                // Create Index
                if (in_array($requestedUpdatedTableIndex['Type'], ['Server', 'Both'])) {
                    foreach ($selectedDatabasesForUpdate as $database) {
                        $sql = MysqlQueryGenerator::getAddIndexSql($database, $table->Name, $requestedUpdatedTableIndex);
                        $sqlQueries[] = $sql;
                    }
                }
            }

            if (count($sqlQueries)) {
                $data['sqlPreview'] = true;
                $data['sqlPreviews'] = collect($sqlQueries)->map(function ($sqlQuery) {
                    return SqlFormatter::format($sqlQuery);
                })->toArray();
            }
        } elseif ($table->Type == 'Client') {
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

    public function tableIndicesOperationsSaveAndExecute(Request $request): ServiceDto
    {
        $tableId = $request->get('TableId');
        $table = $this->tableRepository->firstByAttributes(
            [
                ['column' => 'Id', 'operand' => '=', 'value' => $tableId]
            ],
            ['companyTables.company', 'module.companies']
        );

        //$tableModuleCompanyIds = $table->module->companies->pluck('Id')->toArray();
        $tableModuleCompanyDatabases = $table->module->companies->pluck('DatabaseName')->toArray();

        //$companyTableCompanyIds = $table->companyTables->pluck('company.Id')->toArray();
        $companyTableDatabases = $table->companyTables->pluck('company.DatabaseName')->toArray();

        $sqlQueries = [];

        // Add Section
        foreach ($request->get('newIndices') as $newIndex) {
            // Entry In TableIndex Table
            $this->entryInTableIndexTable($newIndex);

            if (in_array($newIndex['Type'], ['Server', 'Both'])) {
                // This Field Is CompanySpecific
                $tableIndexSpecificDatabases = $this->getDatabaseNamesByCompanyIds($newIndex['companies']);
                $selectedDatabases = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableIndexSpecificDatabases);

                foreach ($selectedDatabases as $database) {
                    $sql = MysqlQueryGenerator::getAddIndexSql($database, $table->Name, $newIndex);
                    $sqlQueries[] = $sql;
                }
            }
        }

        // Delete Section
        $tableIndicesToDelete = $this->tableIndexRepository->getByAttributes(
            [
                ['column' => 'Id', 'operand' => '=', 'value' => $request->get('tableIndexIdsToDelete')]
            ],
            ['companyTableIndices.company']
        );
        foreach ($tableIndicesToDelete as $tableIndexToDelete) {
            $this->tableIndexRepository->findByIdAndDelete($tableIndexToDelete->Id);
            $this->companyTableIndexRepository->deleteByAttributes([
                ['column' => 'TableIndexId', 'operand' => '=', 'value' => $tableIndexToDelete->Id]
            ]);

            if (in_array($tableIndexToDelete->Type, ['Server', 'Both'])) {
                // Company Specific Index
                $tableIndexSpecificDatabases = $tableIndexToDelete->companyTableIndices->pluck('company.DatabaseName')->toArray();
                $selectedDatabases = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableIndexSpecificDatabases);
                foreach ($selectedDatabases as $database) {
                    $sql = MysqlQueryGenerator::getDeleteIndexSql($database, $table->Name, $tableIndexToDelete->Name);
                    $sqlQueries[] = $sql;
                }
            }
        }

        // Update Section
        $requestedUpdatedTableIndices = $request->get('updatedTableIndices');
        $updatedTableIndicesRows = $this->tableIndexRepository->getByAttributes(
            [
                ['column' => 'Id', 'operand' => '=', 'value' => collect($requestedUpdatedTableIndices)->pluck('Id')->toArray()]
            ],
            ['companyTableIndices.company']
        )->groupBy('Id');
        foreach ($requestedUpdatedTableIndices as $requestedUpdatedTableIndex) {
            $existingTableIndexData = $updatedTableIndicesRows[$requestedUpdatedTableIndex['Id']][0];

            // Update TableIndex Entry
            $this->tableIndexRepository->findByIdAndUpdate($existingTableIndexData->Id, [
                'Name' => $requestedUpdatedTableIndex['Name'],
                'Type' => $requestedUpdatedTableIndex['Type'],
                'Unique' => $requestedUpdatedTableIndex['Unique'],
                'ColumnNames' => implode(',', $requestedUpdatedTableIndex['columns']),
                'Disabled' => 0
            ]);

            /*-------------- Add/Delete Index in/from Company Start -----------------*/
            // Add or delete Indices with
            if (in_array('Company', $requestedUpdatedTableIndex['updatedSections'])) {
                $existingTableIndexCompanyIds = $existingTableIndexData->companyTableIndices->pluck('company.Id')->toArray();
                $requestedCompanies = $this->companyRepository->getByAttributes([
                    ['column' => 'Id', 'operand' => '=', 'value' => $requestedUpdatedTableIndex['companies']]
                ]);
                $requestedCompanyIds = $requestedCompanies->pluck('Id')->toArray();
                // Add CompanyTableIndex Entry
                $newCompanyIds = array_diff($requestedCompanyIds, $existingTableIndexCompanyIds);
                foreach ($newCompanyIds as $companyId) {
                    $this->companyTableIndexRepository->create([
                        'CompanyId' => $companyId,
                        'TableIndexId' => $existingTableIndexData->Id
                    ]);
                }

                // Remove CompanyTableIndex Entry
                $removedCompanyIds = array_diff($existingTableIndexCompanyIds, $requestedCompanyIds);
                $this->companyTableIndexRepository->deleteByAttributes([
                    ['column' => 'TableIndexId', 'operand' => '=', 'value' => $existingTableIndexData->Id],
                    ['column' => 'CompanyId', 'operand' => '=', 'value' => $removedCompanyIds],
                ]);
            }

            $existingTableIndexData = $updatedTableIndicesRows[$requestedUpdatedTableIndex['Id']][0];

            $tableIndexSpecificDatabases = $existingTableIndexData->companyTableIndices->pluck('company.DatabaseName')->toArray();
            $selectedDatabasesForUpdate = $this->getCandidateDatabases($companyTableDatabases, $tableModuleCompanyDatabases, $table, $tableIndexSpecificDatabases);

            // Delete Existing Index from Databases
            if (in_array($existingTableIndexData->Type, ['Server', 'Both'])) {
                foreach ($selectedDatabasesForUpdate as $database) {
                    $sql = MysqlQueryGenerator::getDeleteIndexSql($database, $table->Name, $existingTableIndexData->Name);
                    $sqlQueries[] = $sql;
                }
            }
            // Create Index
            if (in_array($requestedUpdatedTableIndex['Type'], ['Server', 'Both'])) {
                foreach ($selectedDatabasesForUpdate as $database) {
                    $sql = MysqlQueryGenerator::getAddIndexSql($database, $table->Name, $requestedUpdatedTableIndex);
                    $sqlQueries[] = $sql;
                }
            }
            /*-------------- Add/Delete Index in/from Company End -----------------*/
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
                    Log::error("Query execution failed (tableIndicesOperationsSaveAndExecute). \n Query: $sql \n Error Message: {$exception->getMessage()}");
                }
            }
        }

        // Update Table Version
        $this->tableRepository->findByIdAndUpdate($tableId, [
            'Version' => $table->Version + 1
        ]);

        return new ServiceDto(
            "Total: $totalQueries\nSuccess: $successful\nFailed: $failed\nTableIndices Operation Save And Execute Finished Successfully.",
            200,
            []
        );
    }

    /**
     * @param mixed $newIndex
     * @return void
     */
    private function entryInTableIndexTable(mixed $newIndex): void
    {
        $newTableIndex = $this->createTableIndex($newIndex);

        if ($newIndex['companies']) {
            // This Field Is CompanySpecific
            $selectedCompanies = $this->companyRepository->getByAttributes([
                ['column' => 'Id', 'operand' => '=', 'value' => $newIndex['companies']]
            ]);
            $selectedCompanyIds = $selectedCompanies->pluck('Id')->toArray();
            // Entry
            foreach ($selectedCompanyIds as $selectedCompanyId) {
                $this->companyTableIndexRepository->create([
                    'CompanyId' => $selectedCompanyId,
                    'TableIndexId' => $newTableIndex->Id
                ]);
            }
        }
    }

    /**
     * @param array $newIndex
     * @return Model
     */
    private function createTableIndex(array $newIndex): Model
    {
        return $this->tableIndexRepository->create(
            [
                'TableId' => $newIndex['TableId'],
                'Name' => $newIndex['Name'],
                'Type' => $newIndex['Type'],
                'Unique' => $newIndex['Unique'],
                'ColumnNames' => implode(',', $newIndex['columns']),
                'Disabled' => 0
            ]
        );
    }

    public function tableIndicesOperationsSaveWithoutExecuting(Request $request): ServiceDto
    {
        $tableId = $request->get('TableId');
        $table = $this->tableRepository->firstByAttributes(
            [
                ['column' => 'Id', 'operand' => '=', 'value' => $tableId]
            ],
            ['companyTables.company', 'module.companies']
        );

        //$tableModuleCompanyDatabases = $table->module->companies->pluck('DatabaseName')->toArray();
        //$companyTableDatabases = $table->companyTables->pluck('company.DatabaseName')->toArray();

        // Add Section
        foreach ($request->get('newIndices') as $newIndex) {
            // Entry In TableIndex Table
            $this->entryInTableIndexTable($newIndex);
        }

        // Delete Section
        $tableIndicesToDelete = $this->tableIndexRepository->getByAttributes(
            [
                ['column' => 'Id', 'operand' => '=', 'value' => $request->get('tableIndexIdsToDelete')]
            ],
            ['companyTableIndices.company']
        );

        foreach ($tableIndicesToDelete as $tableIndexToDelete) {
            $this->tableIndexRepository->findByIdAndDelete($tableIndexToDelete->Id);
            $this->companyTableIndexRepository->deleteByAttributes([
                ['column' => 'TableIndexId', 'operand' => '=', 'value' => $tableIndexToDelete->Id]
            ]);
        }

        // Update Section
        $requestedUpdatedTableIndices = $request->get('updatedTableIndices');
        $updatedTableIndicesRows = $this->tableIndexRepository->getByAttributes(
            [
                ['column' => 'Id', 'operand' => '=', 'value' => collect($requestedUpdatedTableIndices)->pluck('Id')->toArray()]
            ],
            ['companyTableIndices.company']
        )->groupBy('Id');

        foreach ($requestedUpdatedTableIndices as $requestedUpdatedTableIndex) {
            $existingTableIndexData = $updatedTableIndicesRows[$requestedUpdatedTableIndex['Id']][0];

            // Update TableIndex Entry
            $this->tableIndexRepository->findByIdAndUpdate($existingTableIndexData->Id, [
                'Name' => $requestedUpdatedTableIndex['Name'],
                'Type' => $requestedUpdatedTableIndex['Type'],
                'Unique' => $requestedUpdatedTableIndex['Unique'],
                'ColumnNames' => implode(',', $requestedUpdatedTableIndex['columns']),
                'Disabled' => 0
            ]);

            /*-------------- Add/Delete Index in/from Company Start -----------------*/
            // Add or delete columns with
            if (in_array('Company', $requestedUpdatedTableIndex['updatedSections'])) {
                $existingTableIndexCompanyIds = $existingTableIndexData->companyTableIndices->pluck('company.Id')->toArray();
                $requestedCompanies = $this->companyRepository->getByAttributes([
                    ['column' => 'Id', 'operand' => '=', 'value' => $requestedUpdatedTableIndex['companies']]
                ]);
                $requestedCompanyIds = $requestedCompanies->pluck('Id')->toArray();
                // Add CompanyTableIndex Entry
                $newCompanyIds = array_diff($requestedCompanyIds, $existingTableIndexCompanyIds);
                foreach ($newCompanyIds as $companyId) {
                    $this->companyTableIndexRepository->create([
                        'CompanyId' => $companyId,
                        'TableIndexId' => $existingTableIndexData->Id
                    ]);
                }

                // Remove CompanyTableIndex Entry
                $removedCompanyIds = array_diff($existingTableIndexCompanyIds, $requestedCompanyIds);
                $this->companyTableIndexRepository->deleteByAttributes([
                    ['column' => 'TableIndexId', 'operand' => '=', 'value' => $existingTableIndexData->Id],
                    ['column' => 'CompanyId', 'operand' => '=', 'value' => $removedCompanyIds],
                ]);
            }
            /*-------------- Add/Delete Index in/from Company End -----------------*/
        }


        return new ServiceDto("TableIndices Operation Save without Executing Finished Successfully.", 200, []);
    }
}
