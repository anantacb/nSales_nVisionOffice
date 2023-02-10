<?php

namespace App\Services\TableHelper;


use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

class TableHelperService implements TableHelperServiceInterface
{
    public function __construct()
    {
    }

    public function getEnumValues(Request $request): ServiceDto
    {
        $enumValues = [];
        $namespaced_model = "App\Models\\" . $request->get("database_type") . "\\" . $request->get('table');
        $model = new $namespaced_model();
        $enumValues = $model->getEnumColumnValues($request->get('column'));
        return new ServiceDto("EnumValues Retrieved Successfully . ", 200, $enumValues);
    }
}
