<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CompanyUserRole extends BaseModel
{
    protected $table = 'CompanyUserRole';

    public function role(): HasOne
    {
        return $this->hasOne(Role::class, 'Id', 'RoleId');
    }
}
