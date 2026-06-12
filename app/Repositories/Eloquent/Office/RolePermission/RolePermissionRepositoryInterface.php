<?php

namespace App\Repositories\Eloquent\Office\RolePermission;

use App\Repositories\Eloquent\Base\BaseRepositoryInterface;

interface RolePermissionRepositoryInterface extends BaseRepositoryInterface
{
    public function syncForRole(int $roleId, array $permissionIds): void;
}
