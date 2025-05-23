<?php

namespace App\Services\Deployment;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface DeploymentServiceInterface
{
    public function getCompanyDeploymentStatus(Request $request): ServiceDto;

    public function startCompanyDeployment(Request $request): ServiceDto;

}
