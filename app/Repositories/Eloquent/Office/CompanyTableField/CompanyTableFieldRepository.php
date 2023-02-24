<?php

namespace App\Repositories\Eloquent\Office\CompanyTableField;

use App\Models\Office\CompanyTableField;
use App\Repositories\Eloquent\Base\BaseRepository;

class CompanyTableFieldRepository extends BaseRepository implements CompanyTableFieldRepositoryInterface
{
    public function __construct(CompanyTableField $model)
    {
        parent::__construct($model);
    }
}
