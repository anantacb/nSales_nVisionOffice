<?php

namespace App\Services\CompanyTranslation;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface CompanyTranslationServiceInterface
{
    public function getCompanyTranslations(Request $request): ServiceDto;

    public function create(Request $request): ServiceDto;

    public function details(Request $request): ServiceDto;

    public function update(Request $request): ServiceDto;

    public function delete(Request $request): ServiceDto;
}
