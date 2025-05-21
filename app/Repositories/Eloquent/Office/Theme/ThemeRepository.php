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

    public function getThemes()
    {
        return $this->model
            ->with("parent")
            ->withCount(['companyTheme' => function ($query) {
                return $query->where("Disabled", 0);
            }])
            ->get();
    }

    public function getTheme($themeId)
    {
        return $this->model
            ->with("companyTheme.company")
            ->where("Id", $themeId)
            ->first();
    }

    public function getCompanyTheme($companyId)
    {
        return $this->companyTheme
            ->with('theme')
            ->where("CompanyId", $companyId)
            ->get();
    }
}
