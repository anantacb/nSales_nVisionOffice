<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RolePermission extends BaseModel
{
    protected $table = 'RolePermission';

    public function permission(): HasOne
    {
        return $this->hasOne(Permission::class, 'Id', 'PermissionId');
    }
}
