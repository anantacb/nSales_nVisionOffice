<?php

namespace App\Services\DataFilter;

use App\Contracts\ServiceDto;
use App\Helpers\SqlFormatter;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyUser\CompanyUserRepositoryInterface;
use App\Repositories\Eloquent\Office\DataFilter\DataFilterRepositoryInterface;
use App\Repositories\Eloquent\Office\Table\TableRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataFilterService implements DataFilterServiceInterface
{
    protected DataFilterRepositoryInterface $dataFilterRepository;
    protected TableRepositoryInterface $tableRepository;
    protected CompanyUserRepositoryInterface $companyUserRepository;
    protected CompanyRepositoryInterface $companyRepository;

    public function __construct(
        DataFilterRepositoryInterface  $dataFilterRepository,
        TableRepositoryInterface       $tableRepository,
        CompanyUserRepositoryInterface $companyUserRepository,
        CompanyRepositoryInterface     $companyRepository
    )
    {
        $this->dataFilterRepository = $dataFilterRepository;
        $this->tableRepository = $tableRepository;
        $this->companyUserRepository = $companyUserRepository;
        $this->companyRepository = $companyRepository;
    }

    public function create(Request $request): ServiceDto
    {
        $ApplyTo = $request->get('ApplyTo');
        $dataFilter = $this->dataFilterRepository->create([
            'Name' => $request->get('Name'),
            'Type' => $request->get('Type'),
            'Description' => $request->get('Description'),
            'Disabled' => $request->get('Disabled'),

            'Value' => $request->get('Value'),
            'ValueExpression' => $request->get('ValueExpression'),

            'ModuleId' => $request->get('ModuleId'),
            'TableId' => $request->get('TableId'),
            'ApplicationId' => $ApplyTo == 'Application' ? $request->get('ApplicationId') : null,
            'CompanyId' => $ApplyTo == 'Company' ? $request->get('CompanyId') : null,
            'RoleId' => $ApplyTo == 'Role' ? $request->get('RoleId') : null,
            'CompanyUserId' => $ApplyTo == 'User' ? $request->get('CompanyUserId') : null,
        ]);
        return new ServiceDto("DataFilter Created Successfully.", 200, $dataFilter);
    }

    public function update(Request $request): ServiceDto
    {
        $ApplyTo = $request->get('ApplyTo');

        $dataFilter = $this->dataFilterRepository->findByIdAndUpdate(
            $request->get('Id'),
            [
                'Name' => $request->get('Name'),
                'Type' => $request->get('Type'),
                'Description' => $request->get('Description'),
                'Disabled' => $request->get('Disabled'),

                'Value' => $request->get('Value'),
                'ValueExpression' => $request->get('ValueExpression'),

                'ModuleId' => $request->get('ModuleId'),
                'TableId' => $request->get('TableId'),
                'ApplicationId' => $ApplyTo == 'Application' ? $request->get('ApplicationId') : null,
                'CompanyId' => $ApplyTo == 'Company' ? $request->get('CompanyId') : null,
                'RoleId' => $ApplyTo == 'Role' ? $request->get('RoleId') : null,
                'CompanyUserId' => $ApplyTo == 'User' ? $request->get('CompanyUserId') : null,
            ]
        );
        return new ServiceDto("DataFilter Updated Successfully.", 200, $dataFilter);
    }

    public function getDataFilters(Request $request): ServiceDto
    {
        $request = $request->all();
        $request['relations'] = [
            [
                "name" => "module", "columns" => ['Id', 'Name']
            ],
            [
                "name" => "table", "columns" => ['Id', 'Name']
            ],
            [
                "name" => "application", "columns" => ['Id', 'Name']
            ],
            [
                "name" => "role", "columns" => ['Id', 'Name']
            ],
            [
                "name" => "company", "columns" => ['Id', 'Name']
            ],
            [
                "name" => "user", "columns" => ['UserId', 'Name']
            ],
        ];
        $dataFilters = $this->dataFilterRepository->paginatedData($request);
        return new ServiceDto("DataFilters retrieved!!!", 200, $dataFilters);
    }

    public function getCompanyDataFilters(Request $request): ServiceDto
    {
        $request = $request->all();
        $request['relations'] = [
            [
                "name" => "module", "columns" => ['Id', 'Name']
            ],
            [
                "name" => "table", "columns" => ['Id', 'Name']
            ],
            [
                "name" => "application", "columns" => ['Id', 'Name']
            ],
            [
                "name" => "role", "columns" => ['Id', 'Name']
            ],
            [
                "name" => "company", "columns" => ['Id', 'Name']
            ],
            [
                "name" => "user", "columns" => ['UserId', 'Name']
            ],
        ];
        $dataFilters = $this->dataFilterRepository->paginatedCompanyWiseData($request);
        return new ServiceDto("DataFilters retrieved!!!", 200, $dataFilters);
    }

    public function delete(Request $request): ServiceDto
    {
        $this->dataFilterRepository->findByIdAndDelete($request->get('DataFilterId'));
        return new ServiceDto("DataFilter Deleted Successfully.", 200);
    }

    public function details(Request $request): ServiceDto
    {
        $relations = [
            'role' => function ($q) {
                $q->select(['Id', 'Type', 'CompanyId', 'Name']);
            },
            'companyUser' => function ($q) {
                $q->select(['Id', 'UserId', 'CompanyId']);
            }
        ];

        $dataFilter = $this->dataFilterRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $request->get('DataFilterId')]
        ], $relations);

        return new ServiceDto("DataFilter Retrieved Successfully.", 200, $dataFilter);
    }

    public function getFilterResult(Request $request): ServiceDto
    {
        $requestedDataFilter = $this->dataFilterRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $request->get('DataFilterId')]
        ]);

        $table = $this->tableRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $requestedDataFilter->TableId]
        ]);

        $companyUserRelations = [
            'companyUserRoles', 'user'
        ];
        $companyUser = $this->companyUserRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $request->get('CompanyUserId')]
        ], $companyUserRelations);

        $company = $this->companyRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $companyUser->CompanyId]
        ]);

        $dataFilters = $this->dataFilterRepository
            ->getModel()
            ->where('TableId', $requestedDataFilter->TableId)
            ->where(function ($query) use ($companyUser) {
                $query->where('CompanyId', $companyUser->CompanyId)
                    ->orWhere('CompanyUserId', $companyUser->Id)
                    ->orWhereIn('RoleId', $companyUser->companyUserRoles->pluck('RoleId')->toArray());
            })
            ->where('Disabled', 0)
            ->orderBy('Id')
            ->get();

        $raw_query = '1';
        foreach ($dataFilters as $dataFilter) {
            if ($dataFilter->Value) {
                $dataFilter->Value = trim($dataFilter->Value);
                if (!(str_starts_with($dataFilter->Value, 'AND') || str_starts_with($dataFilter->Value, 'OR'))) {
                    $raw_query .= " AND";
                }
                $raw_query .= " $dataFilter->Value";
            }
            if ($dataFilter->ValueExpression) {
                $dataFilter->ValueExpression = trim($dataFilter->ValueExpression);
                if (!(str_starts_with($dataFilter->ValueExpression, '"AND') || str_starts_with($dataFilter->ValueExpression, '"OR'))) {
                    $raw_query .= " AND";
                }
                preg_match_all("/(\"\+\s[.\w]+\s\+\")/", $dataFilter->ValueExpression, $matches);
                foreach ($matches[1] as $match) {
                    $expression = $match;
                    $expression = str_replace("\"+", "", $expression);
                    $expression = str_replace("+\"", "", $expression);
                    $expression = trim($expression);
                    $expression = explode(".", $expression);
                    $related_data = $expression[0];
                    $column = $expression[1];
                    $replace_value = "";
                    switch ($related_data) {
                        case "CompanyUser":
                            $replace_value = $companyUser->$column;
                            break;
                        case "User":
                            $replace_value = $companyUser->user->$column;
                            break;
                        default:
                            break;
                    }
                    $pos = strpos($dataFilter->ValueExpression, $match);
                    if ($pos !== false) {
                        $dataFilter->ValueExpression = substr_replace($dataFilter->ValueExpression, $replace_value, $pos, strlen($match));
                    }
                }
                // Remove Starting and Ending Double Quotation (")
                $dataFilter->ValueExpression = substr($dataFilter->ValueExpression, 1, -1);
                $raw_query .= " " . $dataFilter->ValueExpression;
            }
        }

        $query = "SELECT * FROM `{$company->DatabaseName}`.`{$table->Name}` WHERE " . $raw_query . ";";

        $queryRowCount = "SELECT COUNT(*) as Count FROM `{$company->DatabaseName}`.`{$table->Name}` WHERE " . $raw_query;

        $rowCount = DB::select($queryRowCount)[0]->Count;

        return new ServiceDto("DataFilter Result Retrieved Successfully.", 200, [
            'Query' => SqlFormatter::format($query),
            'RowCount' => $rowCount
        ]);
    }
}
