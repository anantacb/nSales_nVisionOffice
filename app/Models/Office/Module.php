<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends BaseModel
{
    use HasFactory;

    protected $table = 'Module';

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, CompanyModule::class, 'ModuleId', 'CompanyId');
    }

    public function moduleSettings(): HasMany
    {
        return $this->hasMany(ModuleSetting::class, 'ModuleId', 'Id');
    }

    public function tables(): HasMany
    {
        return $this->hasMany(Table::class, 'ModuleId', 'Id');
    }

    public function subModules(): HasMany
    {
        return $this->hasMany(Module::class, 'ModuleId', 'Id');
    }

    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(Application::class, ApplicationModule::class, 'ModuleId', 'ApplicationId');
    }
}
