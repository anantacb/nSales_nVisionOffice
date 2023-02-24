<?php

namespace App\Repositories\Eloquent\Office\CompanyModule;

use App\Models\Office\CompanyModule;
use App\Repositories\Eloquent\Base\BaseRepository;

class CompanyModuleRepository extends BaseRepository implements CompanyModuleRepositoryInterface
{
    public function __construct(CompanyModule $model)
    {
        parent::__construct($model);
    }
}
