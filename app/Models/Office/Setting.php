<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends BaseModel
{
    use HasFactory;

    protected $table = 'Setting';

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'CompanyId', 'Id');
    }

    public function moduleSetting(): BelongsTo
    {
        return $this->belongsTo(ModuleSetting::class, 'ModuleSettingId', 'Id');
    }
}
