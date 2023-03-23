<?php

namespace App\Services\Company;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface CompanyServiceInterface
{
    public function getAllCompanies(Request $request): ServiceDto;

    public function getModuleEnabledCompanies(Request $request): ServiceDto;

    public function getCompanies(Request $request): ServiceDto;

    public function create(Request $request): ServiceDto;

    public function details(Request $request): ServiceDto;

    public function update(Request $request): ServiceDto;

    public function delete(Request $request): ServiceDto;
}
