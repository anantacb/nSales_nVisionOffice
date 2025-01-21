<?php

namespace App\Services\Onboard;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface OnboardServiceInterface
{
    public function getCompanyOnboardStatus(Request $request): ServiceDto;

    public function updateCompanyOnboardStatus(Request $request): ServiceDto;
}
