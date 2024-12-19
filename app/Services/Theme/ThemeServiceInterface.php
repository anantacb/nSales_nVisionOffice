<?php

namespace App\Services\Theme;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface ThemeServiceInterface
{
    public function getCompanyTheme(Request $request): ServiceDto;
}
