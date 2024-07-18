<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModulePackage extends BaseModel
{
    protected $table = 'ModulePackage';

    public function modulePackageModules(): HasMany
    {
        return $this->hasMany(ModulePackageModule::class, 'ModulePackageId', 'Id');
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, ModulePackageModule::class, 'ModulePackageId', 'ModuleId');
    }
}
