<?php

namespace App\Repositories\Eloquent\Office\Role;

use App\Models\Office\Role;
use App\Repositories\Eloquent\Base\BaseRepository;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }
}
