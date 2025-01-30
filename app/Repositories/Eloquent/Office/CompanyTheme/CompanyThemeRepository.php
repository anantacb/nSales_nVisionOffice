<?php

namespace App\Repositories\Eloquent\Office\CompanyTheme;

use App\Models\Office\CompanyTheme;
use App\Repositories\Eloquent\Base\BaseRepository;

class CompanyThemeRepository extends BaseRepository implements CompanyThemeRepositoryInterface
{
    public function __construct(CompanyTheme $model)
    {
        parent::__construct($model);
    }
}
