<?php

namespace App\Repositories\Eloquent\Office\Theme;

use App\Models\Office\CompanyTheme;
use App\Models\Office\Theme;
use App\Repositories\Eloquent\Base\BaseRepository;

class ThemeRepository extends BaseRepository implements ThemeRepositoryInterface
{
    private CompanyTheme $companyTheme;

    public function __construct(Theme $model, CompanyTheme $companyTheme)
    {
        parent::__construct($model);
        $this->companyTheme = $companyTheme;
    }

    public function getCompanyTheme($companyId)
    {
        return $this->companyTheme
            ->with('theme')
            ->where("CompanyId", $companyId)
            ->get();
    }
}
