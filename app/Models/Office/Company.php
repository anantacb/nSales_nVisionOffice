<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company extends BaseModel
{
    use HasFactory;

    protected $table = 'Company';

    /*protected $casts = [
        'InsertTime' => 'datetime:Y-m-d H:i:s',
        'UpdateTime' => 'datetime:Y-m-d H:i:s',
        'TrialStartDate' => 'datetime:Y-m-d H:i:s',
    ];*/

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, CompanyModule::class, 'CompanyId', 'ModuleId');
    }
}
