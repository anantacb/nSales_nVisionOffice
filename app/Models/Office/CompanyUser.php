<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyUser extends BaseModel
{
    protected $table = 'CompanyUser';

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'CompanyUserRole', 'CompanyUserId', 'RoleId', 'Id', 'Id');
    }

    public function companyUserRoles(): HasMany
    {
        return $this->hasMany(CompanyUserRole::class, 'CompanyUserId', 'Id');
    }

    public function scopeOfCompany($query, $CompanyId)
    {
        return $query->where('CompanyId', $CompanyId);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UserId', 'Id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'CompanyId', 'Id');
    }
}
