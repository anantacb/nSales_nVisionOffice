<?php

namespace App\Services\TableHelper;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface TableHelperServiceInterface
{
    public function getEnumValues(Request $request): ServiceDto;

    public function getColumnDistinctValues(Request $request): ServiceDto;

    public function getAllTableColumnNames(Request $request): ServiceDto;
}
