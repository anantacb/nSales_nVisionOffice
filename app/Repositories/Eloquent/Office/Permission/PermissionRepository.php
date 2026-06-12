<?php

namespace App\Repositories\Eloquent\Office\Permission;

use App\Models\Office\Permission;
use App\Repositories\Eloquent\Base\BaseRepository;

class PermissionRepository extends BaseRepository implements PermissionRepositoryInterface
{
    public function __construct(Permission $model)
    {
        parent::__construct($model);
    }
}
