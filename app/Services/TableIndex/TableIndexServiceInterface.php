<?php

namespace App\Services\TableIndex;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface TableIndexServiceInterface
{
    public function getTableIndices(Request $request): ServiceDto;

    public function getTableIndicesOperationPreviews(Request $request): ServiceDto;

    public function tableIndicesOperationsSaveAndExecute(Request $request): ServiceDto;

    public function tableIndicesOperationsSaveWithoutExecuting(Request $request): ServiceDto;
}
