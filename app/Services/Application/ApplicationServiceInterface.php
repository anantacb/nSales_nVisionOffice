<?php

namespace App\Services\Application;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface ApplicationServiceInterface
{
    public function getAllApplications(Request $request): ServiceDto;
}
