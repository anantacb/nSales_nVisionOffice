<?php

namespace App\Repositories\Eloquent\Office\User;

use App\Models\Office\CompanyUser;
use App\Repositories\Eloquent\Base\BaseRepository;

class CompanyUserRepository extends BaseRepository implements CompanyUserRepositoryInterface
{
    public function __construct(CompanyUser $model)
    {
        parent::__construct($model);
    }
}
