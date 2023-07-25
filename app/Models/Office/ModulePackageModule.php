<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ModulePackageModule extends BaseModel
{


    protected $table = 'ModulePackageModule';

    public function module(): HasOne
    {
        return $this->hasOne(Module::class, 'Id', 'ModuleId');
    }
}
