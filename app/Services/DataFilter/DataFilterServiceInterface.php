<?php

namespace App\Services\DataFilter;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface DataFilterServiceInterface
{
    public function getDataFilters(Request $request): ServiceDto;

    public function getCompanyDataFilters(Request $request): ServiceDto;

    public function create(Request $request): ServiceDto;

    public function update(Request $request): ServiceDto;

    public function delete(Request $request): ServiceDto;

    public function details(Request $request): ServiceDto;

    public function getFilterResult(Request $request): ServiceDto;
}
