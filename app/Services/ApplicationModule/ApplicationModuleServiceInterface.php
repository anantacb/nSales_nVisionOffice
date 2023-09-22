<?php

namespace App\Services\ApplicationModule;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface ApplicationModuleServiceInterface
{
    public function create(Request $request): ServiceDto;

    public function update(Request $request): ServiceDto;

    public function delete(Request $request): ServiceDto;
}
