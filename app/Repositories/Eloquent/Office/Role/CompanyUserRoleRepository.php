<?php

namespace App\Repositories\Eloquent\Office\Role;

use App\Models\Office\CompanyUserRole;
use App\Repositories\Eloquent\Base\BaseRepository;

class CompanyUserRoleRepository extends BaseRepository implements CompanyUserRoleRepositoryInterface
{
    public function __construct(CompanyUserRole $model)
    {
        parent::__construct($model);
    }
}
