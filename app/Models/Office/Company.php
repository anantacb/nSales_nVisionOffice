<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends BaseModel
{
    use HasFactory;

    protected $table = 'Company';

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, CompanyModule::class, 'CompanyId', 'ModuleId');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class, 'CompanyId', 'Id');
    }
}
