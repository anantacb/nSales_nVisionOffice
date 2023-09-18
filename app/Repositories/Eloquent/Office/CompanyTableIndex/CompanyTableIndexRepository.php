<?php

namespace App\Repositories\Eloquent\Office\CompanyTableIndex;

use App\Models\Office\CompanyTableIndex;
use App\Repositories\Eloquent\Base\BaseRepository;

class CompanyTableIndexRepository extends BaseRepository implements CompanyTableIndexRepositoryInterface
{
    public function __construct(CompanyTableIndex $model)
    {
        parent::__construct($model);
    }
}
