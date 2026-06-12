<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends BaseModel
{
    protected $table = 'Permission';

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'ModuleId', 'Id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'ApplicationId', 'Id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'RolePermission', 'PermissionId', 'RoleId', 'Id', 'Id');
    }
}
