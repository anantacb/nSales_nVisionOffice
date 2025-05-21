<?php

namespace App\Services\Google;

use App\Contracts\ServiceDto;

interface GoogleBuildServiceInterface
{
    public function triggerBuild($companyDomain): ServiceDto;
}
