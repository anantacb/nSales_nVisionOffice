<?php

namespace App\Services\TableHelper;


use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\Table\TableRepositoryInterface;
use App\Repositories\Eloquent\Office\TableField\TableFieldRepositoryInterface;
use App\Services\Company\CompanyService;
use Illuminate\Http\Request;

class TableHelperService implements TableHelperServiceInterface
{
    protected TableRepositoryInterface $tableRepository;
    protected TableFieldRepositoryInterface $tableFieldRepository;

    public function __construct(
        TableRepositoryInterface      $tableRepository,
        TableFieldRepositoryInterface $tableFieldRepository,
    )
    {
        $this->tableRepository = $tableRepository;
        $this->tableFieldRepository = $tableFieldRepository;
    }

    public function getColumnDistinctValues(Request $request): ServiceDto
    {
        $databaseType = $request->get("DatabaseType");
        $tableName = $request->get('TableName');
        $columName = $request->get('ColumnName');
        if ($databaseType == 'Company') {
            CompanyService::setCompanyDatabaseConnection($request->get('CompanyId'));
        }
        $namespacedModel = "App\Models\\$databaseType\\$tableName";
        $model = new $namespacedModel();
        $values = $model
            ->where($columName, "!=", "")
            ->whereNotNull($columName)
            ->distinct()
            ->pluck($columName)
            ->toArray();
        return new ServiceDto("Distinct Column Values Retrieved Successfully.", 200, $values);
    }

    public function getValidationArray($tableName, $exceptColumns, $companyId): array
    {
        $table = $this->tableRepository->firstByAttributes(
            [
                ['column' => 'Name', 'operand' => '=', 'value' => $tableName]
            ]
        );
        $generalTableFields = $this->tableFieldRepository->getGeneralTableFields($table->Id);
        $companySpecificTableFields = $this->tableFieldRepository->getCompanySpecificTableFields($table->Id, $companyId);
        $validationRules = [];

        foreach ($generalTableFields as $generalTableField) {
            if (!in_array($generalTableField['Name'], $exceptColumns)) {
                $validationRules[$generalTableField['Name']] = $this->getValidationRule($generalTableField);
            }
        }

        foreach ($companySpecificTableFields as $companySpecificTableField) {
            if (!in_array($companySpecificTableField['Name'], $exceptColumns)) {
                $validationRules[$companySpecificTableField['Name']] = $this->getValidationRule($companySpecificTableField);
            }
        }

        return $validationRules;
    }

    private function getValidationRule($tableField)
    {
        $required = 'nullable';
        $in = '';
        $type = '';

        if ($tableField['InputRequired']) {
            $required = 'required';
        }

        if (in_array($tableField['DataType'], ['varchar', 'text', 'tinytext'])) {
            $type = 'string';
        } elseif ($tableField['DataType'] == 'int') {
            $type = 'integer';
        } elseif ($tableField['DataType'] == 'double') {
            $type = 'numeric';
        } elseif (in_array($tableField['DataType'], ['longtext', 'blob', 'longblob'])) {
            $type = 'string';
        } elseif (in_array($tableField['DataType'], ['timestamp', 'datetime'])) {
            $type = 'date';
        } elseif ($tableField['DataType'] == 'tinyint') {
            $type = 'boolean';
            $in = 'in:0,1';
        } else {
            $in = 'in:' . $this->getEnumValuesFromDataType($tableField['DataType']);
        }

        return $required . '|' . $type . ($in ? '|' . $in : '');
    }

    private function getEnumValuesFromDataType($enumString): array|string
    {
        $enumString = substr($enumString, 5, strlen($enumString) - 6);
        return str_replace(['\'', ' '], '', $enumString);
    }

    public function getEnumValues(Request $request): ServiceDto
    {
        $databaseType = $request->get("DatabaseType");
        $tableName = $request->get('TableName');
        $columName = $request->get('ColumnName');
        if ($databaseType == 'Company') {
            CompanyService::setCompanyDatabaseConnection($request->get('CompanyId'));
        }
        $namespacedModel = "App\Models\\$databaseType\\$tableName";
        $model = new $namespacedModel();
        $enumValues = $model->getEnumColumnValues($columName);
        return new ServiceDto("EnumValues Retrieved Successfully.", 200, $enumValues);
    }
}
