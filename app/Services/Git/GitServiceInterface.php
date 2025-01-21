<?php

namespace App\Services\Git;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface GitServiceInterface
{
    public function getCompanyBranches(Request $request): ServiceDto;

    public function createCompanyBranches(Request $request): ServiceDto;
}
