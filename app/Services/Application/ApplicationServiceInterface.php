<?php

namespace App\Services\Application;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface ApplicationServiceInterface
{
    public function getAllApplications(Request $request): ServiceDto;

    public function getApplications(Request $request): ServiceDto;

    public function create(Request $request): ServiceDto;

    public function details(Request $request): ServiceDto;

    public function update(Request $request): ServiceDto;

    public function delete(Request $request): ServiceDto;
}
