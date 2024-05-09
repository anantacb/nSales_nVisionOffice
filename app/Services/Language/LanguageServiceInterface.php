<?php

namespace App\Services\Language;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface LanguageServiceInterface
{
    public function getAllLanguages(Request $request): ServiceDto;

    public function getLanguages(Request $request): ServiceDto;

    public function create(Request $request): ServiceDto;

    public function details(Request $request): ServiceDto;

    public function update(Request $request): ServiceDto;

    public function delete(Request $request): ServiceDto;
}
