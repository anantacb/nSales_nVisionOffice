<?php

namespace App\Services\EmailConfiguration;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface EmailConfigurationServiceInterface
{
    public function create(Request $request): ServiceDto;
}
