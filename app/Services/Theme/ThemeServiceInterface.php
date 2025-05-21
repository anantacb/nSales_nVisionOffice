<?php

namespace App\Services\Theme;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface ThemeServiceInterface
{
    public function getThemes(): ServiceDto;
    public function getCompanyTheme(Request $request): ServiceDto;

    public function triggerBuild($themeId): ServiceDto;
}
