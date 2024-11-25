<?php

namespace App\Services\Translation;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface TranslationServiceInterface
{
    public function getTranslations(Request $request): ServiceDto;

    public function create(Request $request): ServiceDto;

    public function details(Request $request): ServiceDto;

    public function sync(): ServiceDto;

    public function update(Request $request): ServiceDto;

    public function delete(Request $request): ServiceDto;
}
