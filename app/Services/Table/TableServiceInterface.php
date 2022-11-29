<?php

namespace App\Services\Table;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface TableServiceInterface
{
    public function getTables(Request $request): ServiceDto;
}
