<?php

namespace App\Repositories\Eloquent\Office\Table;

use App\Models\Office\CompanyTable;
use App\Repositories\Eloquent\Base\BaseRepository;

class CompanyTableRepository extends BaseRepository implements CompanyTableRepositoryInterface
{
    public function __construct(CompanyTable $model)
    {
        parent::__construct($model);
    }
}
