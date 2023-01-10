<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends BaseModel
{
    use HasFactory;

    protected $table = 'Module';

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, CompanyModule::class, 'ModuleId', 'CompanyId');
    }
}
