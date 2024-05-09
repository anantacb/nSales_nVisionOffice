<?php

namespace App\Services\CompanyLanguage;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface CompanyLanguageServiceInterface
{
    public function getAllCompanyLanguages(Request $request): ServiceDto;

    public function getCompanyLanguages(Request $request): ServiceDto;

    public function addCompanyLanguage(Request $request): ServiceDto;

    public function setAsDefaultLanguage(Request $request): ServiceDto;

    public function delete(Request $request): ServiceDto;
}
