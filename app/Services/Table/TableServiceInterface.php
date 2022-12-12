<?php

namespace App\Services\Table;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface TableServiceInterface
{
    public function getTables(Request $request): ServiceDto;

    public function getCreateTablePreview(Request $request): ServiceDto;

    public function createTableSaveAndExecute(Request $request): ServiceDto;

    public function createTableSaveWithoutExecuting(Request $request): ServiceDto;
}
