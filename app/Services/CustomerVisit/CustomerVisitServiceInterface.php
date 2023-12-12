<?php

namespace App\Services\CustomerVisit;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface CustomerVisitServiceInterface
{
    public function getCustomerVisits(Request $request): ServiceDto;
}
