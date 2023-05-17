<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company extends BaseModel
{
    use HasFactory;

    protected $table = 'Company';

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, CompanyModule::class, 'CompanyId', 'ModuleId');
    }
}
