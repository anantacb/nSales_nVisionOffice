<?php

namespace App\Services\Database;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface DatabaseServiceInterface
{
    public function getAllCompanies(Request $request): ServiceDto;

    public function copyDBtoDevServer(Request $request): ServiceDto;

}
