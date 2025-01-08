<?php

namespace App\Repositories\Eloquent\Company\CompanyEmailLayout;

use App\Models\Company\CompanyEmailLayout;
use App\Repositories\Eloquent\Base\BaseRepository;

class CompanyEmailLayoutRepository extends BaseRepository implements CompanyEmailLayoutRepositoryInterface
{
    public function __construct(CompanyEmailLayout $model)
    {
        parent::__construct($model);
    }
}
