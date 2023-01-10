<?php

namespace App\Services\Module;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface ModuleServiceInterface
{
    public function getAllModules(Request $request): ServiceDto;
}
