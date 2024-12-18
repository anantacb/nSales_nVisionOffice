<?php

namespace App\Repositories\Eloquent\Office\Theme;


use App\Repositories\Eloquent\Base\BaseRepositoryInterface;

interface ThemeRepositoryInterface extends BaseRepositoryInterface
{
    public function getCompanyTheme($companyId);
}
