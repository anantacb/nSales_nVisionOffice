<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyUser extends BaseModel
{
    use HasFactory;

    protected $table = 'CompanyUser';

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'CompanyUserRole', 'CompanyUserId', 'RoleId', 'Id', 'Id');
    }

    public function companyUserRoles(): HasMany
    {
        return $this->hasMany(CompanyUserRole::class, 'CompanyUserId', 'Id');
    }

    public function scopeOfCompany($query, $company_id)
    {
        return $query->where('CompanyId', $company_id);
    }
}
