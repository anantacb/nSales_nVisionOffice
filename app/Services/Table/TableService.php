<?php

namespace App\Services\Table;


use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\Table\TableRepositoryInterface;
use Illuminate\Http\Request;

class TableService implements TableServiceInterface
{
    protected TableRepositoryInterface $tableRepository;

    public function __construct(TableRepositoryInterface $tableRepository)
    {
        $this->tableRepository = $tableRepository;
    }

    public function getTables(Request $request): ServiceDto
    {
        $request = $request->all();
        $request['relations'] = [
            ["name" => "companyTables.Company", "columns" => ['Id', 'Name', 'CompanyName']],
        ];
        /*$request['filter_by_relation'] =
            [
                ["relation" => "companyTables", "column" => "", "operator" => "", "values" => ""]
            ];*/
        //$request['search_columns'] = ["Name"];

        $tables = $this->tableRepository->paginatedData($request);
        return new ServiceDto("Tables retrieved!!!", 200, $tables);
    }
}
