<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModulePackage extends BaseModel
{
    protected $table = 'ModulePackage';

    public function modulePackageModules(): HasMany
    {
        return $this->hasMany(ModulePackageModule::class, 'ModulePackageId', 'Id');
    }
}
